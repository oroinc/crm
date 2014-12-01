<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\NormalizerInterface;
use Oro\Bundle\UserBundle\Model\Gender;
use Oro\Bundle\ImportExportBundle\Serializer\Serializer;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactNormalizer;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\ImportExport\Writer\ReverseWriter;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\ChannelBundle\ImportExport\Helper\ChannelHelper;
use OroCRM\Bundle\MagentoBundle\Service\ImportHelper;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * TODO Should be fixed during CRM-1185
 */
class CustomerSerializer extends AbstractNormalizer implements DenormalizerInterface, NormalizerInterface
{
    const PROCESSOR_ALIAS = 'orocrm_magento';

    /** @var array */
    protected $importFieldsMap = [
        'customer_id' => 'origin_id',
        'firstname'   => 'first_name',
        'lastname'    => 'last_name',
        'email'       => 'email',
        'middlename'  => 'middle_name',
        'prefix'      => 'name_prefix',
        'suffix'      => 'name_suffix',
        'dob'         => 'birthday',
        'taxvat'      => 'vat',
        'gender'      => 'gender'
    ];

    /** @var array */
    protected $addressMageToBapMapping = [
        'prefix'              => '[namePrefix]',
        'firstname'           => '[firstName]',
        'middlename'          => '[middleName]',
        'lastname'            => '[lastName]',
        'suffix'              => '[nameSuffix]',
        'company'             => '[organization]',
        'street'              => '[street]',
        'city'                => '[city]',
        'postcode'            => '[postalCode]',
        'country_id'          => '[country][iso2Code]',
        'region'              => '[regionText]',
        'region_id'           => '[region][code]',
        'created_at'          => '[created]',
        'updated_at'          => '[updated]',
        'customer_address_id' => '[customerAddressId]',
        'telephone'           => '[phone]'
    ];

    protected $contactAddressEntityToMageMapping = [
        'name_prefix'          => 'prefix',
        'first_name'           => 'firstname',
        'middle_name'          => 'middlename',
        'last_name'            => 'lastname',
        'name_suffix'          => 'suffix',
        'organization'         => 'company',
        'street'               => 'street',
        'city'                 => 'city',
        'postal_code'          => 'postcode',
        'country.iso2_code'    => 'country_id',
        'region_text'          => 'region',
        'region.code'          => 'region_id',
        'created'              => 'created_at',
        'updated'              => 'updated_at'
    ];

    /** @var array */
    static protected $objectFields = [
        'store',
        'website',
        'group',
        'addresses',
        'updatedAt',
        'createdAt',
        'birthday'
    ];

    /** @var ChannelHelper */
    protected $channelImportHelper;

    /**
     * @param ImportHelper  $importHelper
     * @param ChannelHelper $channelHelper
     */
    public function __construct(ImportHelper $importHelper, ChannelHelper $channelHelper)
    {
        parent::__construct($importHelper);
        $this->channelImportHelper = $channelHelper;
    }

    /**
     * @param array          $remoteData
     * @param ContactAddress $contactAddr
     * @param int            $originId
     *
     * @return Address
     */
    public function convertMageAddressToAddress($remoteData, ContactAddress $contactAddr, $originId)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $address  = new Address();

        foreach ($this->contactAddressEntityToMageMapping as $bapField => $mageField) {
            if ($mageField === 'country_id') {
                $accessor->setValue(
                    $address,
                    'country',
                    $accessor->getValue($contactAddr, 'country')
                );
            } elseif ($mageField === 'region_id') {
                $accessor->setValue(
                    $address,
                    'region',
                    $accessor->getValue($contactAddr, 'region')
                );
            } elseif ($mageField === 'street' && is_array($remoteData[$mageField])) {
                $accessor->setValue($address, $bapField, $remoteData[$mageField][0]);
                $accessor->setValue($address, 'street2', $remoteData[$mageField][1]);
            } else {
                $accessor->setValue($address, $bapField, $remoteData[$mageField]);
            }
        }

        $accessor->setValue($address, 'contact_address', $contactAddr);
        $accessor->setValue($address, 'origin_id', $originId);
        $accessor->setValue($address, 'types', $accessor->getValue($contactAddr, 'types'));

        return $address;
    }

    /**
     * @param array   $remoteData
     * @param Address $localData
     * @param bool    $processTypes
     *
     * @return array
     */
    public function compareAddresses($remoteData, $localData, $processTypes = true)
    {
        $result = [];

        $addressData   = $this->getBapAddressData($remoteData);
        $remoteAddress = $this->serializer->denormalize(
            $addressData,
            MagentoConnectorInterface::CUSTOMER_ADDRESS_TYPE,
            null,
            [Serializer::PROCESSOR_ALIAS_KEY => self::PROCESSOR_ALIAS]
        );

        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($this->contactAddressEntityToMageMapping as $oroFieldName => $mageFieldName) {
            try {
                $localValue  = $accessor->getValue($localData, $oroFieldName);
                $remoteValue = $accessor->getValue($remoteAddress, $oroFieldName);

                if ($mageFieldName === 'country_id') {
                    $result['country'] = $accessor->getValue($remoteAddress, 'country');
                } elseif ($mageFieldName === 'region_id') {
                    $result['region'] = $accessor->getValue($remoteAddress, 'region');
                } elseif ($remoteValue !== $localValue) {
                    $result[$oroFieldName] = $remoteValue;
                }
            } catch (\Exception $e) {
            }
        }

        if (!empty($result['region'])) {
            unset($result['region_text']);
        }

        $result['types'] = [];
        $result['remove_types'] = [];

        if ($processTypes) {
            if ($remoteData['is_default_billing'] === true) {
                $result['types'][] = 'billing';
            } else {
                $result['remove_types'][] = 'billing';
            }
            if ($remoteData['is_default_shipping'] === true) {
                $result['types'][] = 'shipping';
            } else {
                $result['remove_types'][] = 'shipping';
            }
        }

        return $result;
    }

    /**
     * @param array   $remoteData
     * @param Address $localData
     *
     * @return array
     */
    public function comparePhones($remoteData, $localData)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        if ($accessor->getValue($localData, 'phone') !== $remoteData['telephone']) {
            return [
                'phone' => $remoteData['telephone'],
            ];
        }

        return [];
    }

    /**
     * @param AbstractAddress $addressFields
     * @param array $defaultData
     *
     * @return array
     */
    public function convertToMagentoAddress(AbstractAddress $addressFields, array $defaultData = [])
    {
        $result   = [];
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($this->contactAddressEntityToMageMapping as $oroPropertyPath => $magentoFieldName) {
            try {
                $oroValue = $accessor->getValue($addressFields, $oroPropertyPath);
            } catch (\Exception $e) {
                $oroValue = null;
            }

            if ($oroValue instanceof \DateTime) {
                $result[$magentoFieldName] = $oroValue->format(ReverseWriter::MAGENTO_DATETIME_FORMAT);
            } elseif ($oroPropertyPath === 'street') {
                try {
                    $street2 = $accessor->getValue($addressFields, 'street2');
                } catch (\Exception $e) {
                    $street2 = '';
                }
                $result[$magentoFieldName] = [$oroValue, $street2];
            } else {
                $result[$magentoFieldName] = $oroValue;
            }
        }

        foreach ($defaultData as $field => $value) {
            if (empty($result[$field])) {
                $result[$field] = $value;
            }
        }

        $types = $addressFields->getTypeNames();
        $result['is_default_billing'] = in_array('billing', $types);
        $result['is_default_shipping'] = in_array('shipping', $types);

        return $result;
    }

    /**
     * Get customer values for given magento fields
     *
     * @param Customer $customer
     * @param array    $magentoFields
     *
     * @return array
     */
    public function getCurrentCustomerValues(Customer $customer, $magentoFields)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $result   = [];
        foreach ($magentoFields as $fieldName) {
            $result[$fieldName] = $accessor->getValue(
                $customer,
                $this->importFieldsMap[$fieldName]
            );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $result   = [];

        foreach ($this->importFieldsMap as $magentoName => $oroName) {
            if (empty($context)) {
                $result[$magentoName] = $accessor->getValue($object, $oroName);
            } else {
                if (array_key_exists($oroName, $context)) {
                    $result[$magentoName] = $context[$oroName];
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof Customer;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return $type == MagentoConnectorInterface::CUSTOMER_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        /** @var Customer $resultObject */
        $resultObject = new $class;

        if (!is_array($data)) {
            return $resultObject;
        }

        $mappedData = [];
        foreach ($data as $key => $value) {
            $fieldKey              = isset($this->importFieldsMap[$key]) ? $this->importFieldsMap[$key] : $key;
            $mappedData[$fieldKey] = $value;
        }

        if (!empty($mappedData['birthday'])) {
            $mappedData['birthday'] = substr($mappedData['birthday'], 0, 10);
        }

        if (isset($mappedData['gender']) && !empty($mappedData['gender'])) {
            $gender = strtolower($mappedData['gender']);
            if (in_array($gender, [Gender::FEMALE, Gender::MALE])) {
                $mappedData['gender'] = $gender;
            } else {
                $mappedData['gender'] = null;
            }
        }

        $integration = $this->getIntegrationFromContext($context);
        $resultObject->setChannel($integration);
        $resultObject->setDataChannel($this->channelImportHelper->getChannel($integration));

        $this->setScalarFieldsValues($resultObject, $mappedData);
        $this->setObjectFieldsValues($resultObject, $mappedData, $format, $context);

        return $resultObject;
    }

    /**
     * @param Customer $object
     * @param array    $data
     */
    protected function setScalarFieldsValues(Customer $object, array $data)
    {
        $data = $this->convertToCamelCase($data);
        foreach ($data as $itemName => $item) {
            if (in_array($itemName, static::$objectFields)) {
                continue;
            }

            $method = 'set' . ucfirst($itemName);
            if (method_exists($object, $method)) {
                $object->$method($item);
            }
        }
    }

    /**
     * @param Customer $object
     * @param array    $data
     * @param mixed    $format
     * @param array    $context
     */
    protected function setObjectFieldsValues(Customer $object, array $data, $format = null, array $context = array())
    {
        if (!empty($data['birthday'])) {
            /** @var \DateTime $birthday */
            $birthday = $this->denormalizeObject(
                $data,
                'birthday',
                'DateTime',
                $format,
                array_merge($context, ['type' => 'date'])
            );
            $object->setBirthday($birthday);
            unset($data['birthday']);
        }

        if (!empty($data['created_at'])) {
            /** @var \DateTime $createdAt */
            $createdAt = $this->denormalizeObject(
                $data,
                'created_at',
                'DateTime',
                $format,
                $context
            );
            $object->setCreatedAt($createdAt);
        }

        if (!empty($data['updated_at'])) {
            /** @var \DateTime $updatedAt */
            $updatedAt = $this->denormalizeObject(
                $data,
                'updated_at',
                'DateTime',
                $format,
                $context
            );
            $object->setUpdatedAt($updatedAt);
        }

        $this->setContact($object, $data, $format, $context);
        $this->setWebsite($object, $data, $format, $context);
        $this->setStore($object, $data, $format, $context);
        $this->setGroup($object, $data, $format, $context);
    }

    protected function setGroup(Customer $object, array $data, $format = null, array $context = array())
    {
        /** @var CustomerGroup $group */
        $group = $this->denormalizeObject(
            $data,
            'group',
            MagentoConnectorInterface::CUSTOMER_GROUPS_TYPE,
            $format,
            $context
        );
        if ($group) {
            $group->setChannel($object->getChannel());
            $object->setGroup($group);
        }
    }

    protected function setStore(Customer $object, array $data, $format = null, array $context = array())
    {
        /** @var Store $store */
        $store = $this->denormalizeObject(
            $data,
            'store',
            MagentoConnectorInterface::STORE_TYPE,
            $format,
            $context
        );
        if ($store) {
            $store->setWebsite($object->getWebsite());
            $store->setChannel($object->getChannel());
            $object->setStore($store);
        }
    }

    protected function setWebsite(Customer $object, array $data, $format = null, array $context = array())
    {
        /** @var Website $website */
        $website = $this->denormalizeObject(
            $data,
            'website',
            MagentoConnectorInterface::WEBSITE_TYPE,
            $format,
            $context
        );
        if ($website) {
            $website->setChannel($object->getChannel());
            $object->setWebsite($website);
        }
    }

    protected function setContact(Customer $object, array $data, $format = null, array $context = array())
    {
        $data['contact'] = $this->formatContactData($data);

        /** @var Contact $contact */
        $contact = $this->denormalizeObject(
            $data,
            'contact',
            ContactNormalizer::CONTACT_TYPE,
            $format,
            $context
        );
        if ($contact) {
            $contact->setBirthday($object->getBirthday());
            $object->setContact($contact);
        }

        $this->setAddresses($object, $data, $format, $context);
    }

    protected function setAddresses(Customer $object, array $data, $format = null, array $context = array())
    {
        if (!empty($data['contact']['addresses'])) {
            $data['addresses'] = $data['contact']['addresses'];
            /** @var \Doctrine\Common\Collections\Collection $addresses */
            $addresses = $this->denormalizeObject(
                $data,
                'addresses',
                MagentoConnectorInterface::CUSTOMER_ADDRESSES_TYPE,
                $format,
                $context
            );

            // TODO Should be fixed during CRM-1185
            $originIds = array();
            foreach ($data['addresses'] as $key => $address) {
                if (!empty($address['customerAddressId'])) {
                    $originIds[$key] = $address['customerAddressId'];
                }
            }

            if (!empty($addresses)) {
                $contact = $object->getContact();
                /** @var Address $address */
                foreach ($addresses as $key => $address) {
                    if (!empty($originIds[$key])) {
                        $address->setOriginId($originIds[$key]);
                    }
                    if ($contactPhone = $address->getContactPhone()) {
                        $contactPhone->setOwner($contact);
                    }
                }
                $object->resetAddresses($addresses);
            }
        }
    }

    /**
     * @todo Move to converter CRM-789
     *
     * @param $data
     *
     * @return array
     */
    protected function formatContactData($data)
    {
        $contact           = $this->convertToCamelCase($data);
        $contactFieldNames = array(
            'firstName'  => null,
            'lastName'   => null,
            'middleName' => null,
            'namePrefix' => null,
            'nameSuffix' => null,
            'gender'     => null,
            'addresses'  => [],
            'phones'     => [],
            'emails'     => []
        );
        // fill default values
        $contact = array_merge($contactFieldNames, $contact);

        foreach ($contact['addresses'] as $key => $address) {
            $bapAddress = $this->getBapAddressData(
                $address,
                array(
                     'firstname' => $contact['firstName'],
                     'lastname'  => $contact['lastName']
                )
            );

            // prepare address types
            if (!empty($address['is_default_shipping'])) {
                $bapAddress['types'][] = array('name' => AddressType::TYPE_SHIPPING);
            }
            if (!empty($address['is_default_billing'])) {
                $bapAddress['types'][] = array('name' => AddressType::TYPE_BILLING);
            }

            if (!empty($address['telephone'])) {
                $phone = $address['telephone'];
                $bapAddress['contactPhone'] = array('phone' => $phone);
                if (!$this->arrayHasValueForKey($contact['phones'], 'phone', $phone)) {
                    $contact['phones'][] = array('phone' => $phone);
                }
            }
            $contact['addresses'][$key] = $bapAddress;
        }

        if (!empty($contact['email'])) {
            $contact['emails'][] = array('email' => $contact['email']);
            unset($contact['email']);
        }

        return $contact;
    }

    /**
     * Check if specific value is present in any of the array items at the given key
     *
     * @param array $array
     * @param mixed $key
     * @param mixed $value
     *
     * @return bool
     */
    protected function arrayHasValueForKey($array, $key, $value)
    {
        foreach ($array as $item) {
            if (isset($item[$key]) && $item[$key] == $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get BAP address data based on magento address data.
     *
     * @param array $address
     * @param array $defaultValues
     *
     * @return array
     */
    protected function getBapAddressData(array $address, array $defaultValues = array())
    {
        $bapAddress = array();
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($this->addressMageToBapMapping as $mageKey => $bapKey) {
            $value = null;
            if (array_key_exists($mageKey, $address)) {
                $value = $address[$mageKey];
            }

            if (array_key_exists($bapKey, $defaultValues) && empty($bapAddress[$bapKey])) {
                $value = $defaultValues[$bapKey];
            }
            $propertyAccessor->setValue($bapAddress, $bapKey, $value);
        }

        // Magento API return address as $street1 . "\n" . $street2
        if (strpos($bapAddress['street'], "\n") !== false) {
            list($bapAddress['street'], $bapAddress['street2']) = explode("\n", $bapAddress['street']);
        }

        if (empty($bapAddress['region']['code'])) {
            $bapAddress['region'] = null;
        }

        return $bapAddress;
    }
}
