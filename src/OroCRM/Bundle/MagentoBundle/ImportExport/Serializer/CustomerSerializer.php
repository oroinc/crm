<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use Oro\Bundle\UserBundle\Model\Gender;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;

use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\ImportExport\Writer\ReverseWriter;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\AccountBundle\ImportExport\Serializer\Normalizer\AccountNormalizer;
use OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactNormalizer;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * TODO Should be fixed during CRM-1185
 */
class CustomerSerializer extends AbstractNormalizer implements DenormalizerInterface, NormalizerInterface
{
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
    protected $addressBapToMageMapping = [
        'namePrefix'        => 'prefix',
        'firstName'         => 'firstname',
        'middleName'        => 'middlename',
        'lastName'          => 'lastname',
        'nameSuffix'        => 'suffix',
        'organization'      => 'company',
        'street'            => 'street',
        'city'              => 'city',
        'postalCode'        => 'postcode',
        'country'           => 'country_id',
        'regionText'        => 'region',
        'region'            => 'region_id',
        'created'           => 'created_at',
        'updated'           => 'updated_at',
        'customerAddressId' => 'customer_address_id',
        'phone'             => 'telephone',
        'contactPhone'      => 'telephone',
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
        'region.combined_code' => 'region_id',
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
        $remoteAddress = $this->serializer->denormalize($addressData, MagentoConnectorInterface::CUSTOMER_ADDRESS_TYPE);

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
     *
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
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Customer;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type == MagentoConnectorInterface::CUSTOMER_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $resultObject = new Customer();

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

        $resultObject->setChannel($this->getChannelFromContext($context));
        $this->setScalarFieldsValues($resultObject, $mappedData);
        $this->setObjectFieldsValues($resultObject, $mappedData);

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
        // format contact data
        $data['contact']   = $this->formatContactData($data);
        $data['account']   = $this->formatAccountData($data);
        $data['addresses'] = $data['contact']['addresses'];

        /** @var Contact $contact */
        $contact = $this->denormalizeObject($data, 'contact', ContactNormalizer::CONTACT_TYPE);

        /** @var Account $account */
        $account = $this->denormalizeObject(
            $data,
            'account',
            AccountNormalizer::ACCOUNT_TYPE,
            $format,
            array_merge($context, ['mode' => AccountNormalizer::FULL_MODE])
        );
        unset($data['account']);

        /** @var Website $website */
        $website = $this->denormalizeObject($data, 'website', MagentoConnectorInterface::WEBSITE_TYPE);
        $website->setChannel($object->getChannel());

        /** @var Store $store */
        $store = $this->denormalizeObject($data, 'store', MagentoConnectorInterface::STORE_TYPE);
        $store->setWebsite($website);
        $store->setChannel($object->getChannel());

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
        }

        /** @var CustomerGroup $group */
        $group = $this->denormalizeObject($data, 'group', MagentoConnectorInterface::CUSTOMER_GROUPS_TYPE);
        $group->setChannel($object->getChannel());

        /** @var \DateTime $createdAt */
        $createdAt = $this->denormalizeObject(
            $data,
            'created_at',
            'DateTime',
            $format,
            array_merge($context, ['type' => 'datetime', 'format' => 'Y-m-d H:i:s'])
        );

        /** @var \DateTime $updatedAt */
        $updatedAt = $this->denormalizeObject(
            $data,
            'updated_at',
            'DateTime',
            $format,
            array_merge($context, ['type' => 'datetime', 'format' => 'Y-m-d H:i:s'])
        );
        $object
            ->setWebsite($website)
            ->setStore($store)
            ->setGroup($group)
            ->setContact($contact)
            ->setAccount($account)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($updatedAt);

        /** @var \Doctrine\Common\Collections\Collection $addresses */
        $addresses = $this->denormalizeObject($data, 'addresses', MagentoConnectorInterface::CUSTOMER_ADDRESSES_TYPE);
        if (!empty($addresses)) {
            $object->resetAddresses($addresses);
        }

        $this->setPhoneToTheAddress($object, $data['addresses'], $contact);
    }

    /**
     * @param Customer $customer
     * @param array    $data
     * @param Contact  $contact
     */
    protected function setPhoneToTheAddress($customer, $data, $contact)
    {
        reset($data);

        foreach ($customer->getAddresses() as $address) {
            $mageData = each($data);
            $phone    = new ContactPhone();
            $phone->setPhone($mageData['value']['contactPhone']);
            $phone->setOwner($contact);
            $address->setContactPhone($phone);
            $address->setPhone($mageData['value']['contactPhone']);
        }
    }

    /**
     * @todo Move to converter CRM-789
     *
     * @param $data
     *
     * @return array
     */
    protected function formatAccountData($data)
    {
        $nameParts = array_intersect_key($data, array_flip(['first_name', 'last_name']));
        $account   = ['name' => implode(' ', $nameParts)];

        foreach ($data['addresses'] as $address) {
            $addressTypes = array();
            if (!empty($address['is_default_shipping'])) {
                $addressTypes[] = AddressType::TYPE_SHIPPING . '_address';
            }
            if (!empty($address['is_default_billing'])) {
                $addressTypes[] = AddressType::TYPE_BILLING . '_address';
            }

            foreach ($addressTypes as $addressType) {
                $account[$addressType] = $this->getBapAddressData($address);
            }
        }

        return $account;
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
            'birthday'   => null,
            'phones'     => [],
            'emails'     => []
        );
        // fill default values
        $contact = array_merge($contactFieldNames, $contact);

        foreach ($contact['addresses'] as $key => $address) {
            $bapAddress = $this->getBapAddressData(
                $address,
                array(
                     'firstName' => $contact['firstName'],
                     'lastName'  => $contact['lastName']
                )
            );

            // prepare address types
            if (!empty($address['is_default_shipping'])) {
                $bapAddress['types'][] = AddressType::TYPE_SHIPPING;
            }
            if (!empty($address['is_default_billing'])) {
                $bapAddress['types'][] = AddressType::TYPE_BILLING;
            }

            if (!empty($address['telephone']) && !in_array($address['telephone'], $contact['phones'])) {
                $contact['phones'][] = $address['telephone'];
            }
            $contact['addresses'][$key] = $bapAddress;
        }

        if (!empty($contact['email'])) {
            $contact['emails'][] = $contact['email'];
            unset($contact['email']);
        }

        return $contact;
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
        foreach ($this->addressBapToMageMapping as $bapKey => $mageKey) {
            if (array_key_exists($mageKey, $address)) {
                $bapAddress[$bapKey] = $address[$mageKey];
            } else {
                $bapAddress[$bapKey] = null;
            }

            if (array_key_exists($bapKey, $defaultValues) && empty($bapAddress[$bapKey])) {
                $bapAddress[$bapKey] = $defaultValues[$bapKey];
            }
        }

        // Magento API return address as $street1 . "\n" . $street2
        if (strpos($bapAddress['street'], "\n") !== false) {
            list($bapAddress['street'], $bapAddress['street2']) = explode("\n", $bapAddress['street']);
        }

        return $bapAddress;
    }

    /**
     * @param array  $data
     * @param string $name
     * @param string $type
     * @param mixed  $format
     * @param array  $context
     *
     * @return null|object
     */
    protected function denormalizeObject(array $data, $name, $type, $format = null, $context = array())
    {
        $result = null;
        if (!empty($data[$name])) {
            $result = $this->serializer->denormalize($data[$name], $type, $format, $context);
        }

        return $result;
    }
}
