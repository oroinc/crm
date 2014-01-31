<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

use Oro\Bundle\AddressBundle\Entity\AddressType;

use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\AccountBundle\ImportExport\Serializer\Normalizer\AccountNormalizer;
use OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactNormalizer;

class CustomerDenormalizer extends AbstractNormalizer implements DenormalizerInterface
{
    /**
     * @var array
     */
    protected $importFieldsMap = array(
        'customer_id' => 'origin_id',
        'firstname'   => 'first_name',
        'lastname'    => 'last_name',
        'middlename'  => 'middle_name',
        'prefix'      => 'name_prefix',
        'suffix'      => 'name_suffix',
        'dob'         => 'birthday',
        'taxvat'      => 'vat',
    );

    /**
     * @var array
     */
    protected $addressBapToMageMapping = array(
        'namePrefix' => 'prefix',
        'firstName' => 'firstname',
        'middleName' => 'middlename',
        'lastName' => 'lastname',
        'nameSuffix' => 'suffix',
        'organization' => 'company',
        'street' => 'street',
        'city' => 'city',
        'postalCode' => 'postcode',
        'country' => 'country_id',
        'regionText' => 'region',
        'region' => 'region_id',
        'created' => 'created_at',
        'updated' => 'updated_at'
    );

    /**
     * @var array
     */
    static protected $objectFields = array(
        'store',
        'website',
        'group',
        'addresses',
        'updatedAt',
        'createdAt',
        'birthday'
    );

    /**
     * For importing customers
     *
     * @param mixed  $data
     * @param string $class
     * @param null   $format
     * @param array  $context
     *
     * @return object|Customer
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $resultObject = new Customer();

        $mappedData = [];
        foreach ($data as $key => $value) {
            $fieldKey              = isset($this->importFieldsMap[$key]) ? $this->importFieldsMap[$key] : $key;
            $mappedData[$fieldKey] = $value;
        }

        if (!empty($mappedData['birthday'])) {
            $mappedData['birthday'] = substr($mappedData['birthday'], 0, 10);
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
            $object->setBirthday(
                $this->denormalizeObject(
                    $data,
                    'birthday',
                    'DateTime',
                    $format,
                    array_merge($context, ['type' => 'date'])
                )
            );
        }

        $group = $this->denormalizeObject($data, 'group', MagentoConnectorInterface::CUSTOMER_GROUPS_TYPE);
        $group->setChannel($object->getChannel());

        $object
            ->setWebsite($website)
            ->setStore($store)
            ->setGroup($group)
            ->setContact($contact)
            ->setAccount($account)
            ->setCreatedAt(
                $this->denormalizeObject(
                    $data,
                    'created_at',
                    'DateTime',
                    $format,
                    array_merge($context, ['type' => 'datetime', 'format' => 'Y-m-d H:i:s'])
                )
            )
            ->setUpdatedAt(
                $this->denormalizeObject(
                    $data,
                    'updated_at',
                    'DateTime',
                    $format,
                    array_merge($context, ['type' => 'datetime', 'format' => 'Y-m-d H:i:s'])
                )
            );

        $addresses = $this->denormalizeObject($data, 'addresses', MagentoConnectorInterface::CUSTOMER_ADDRESSES_TYPE);
        if (!empty($addresses)) {
            $object->resetAddresses($addresses);
        }
    }

    /**
     * @todo Move to converter CRM-789
     * @param $data
     * @return array
     */
    protected function formatAccountData($data)
    {
        $account = array(
            'name' => sprintf("%s %s", $data['first_name'], $data['last_name'])
        );

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
     * @param $data
     * @return array
     */
    protected function formatContactData($data)
    {
        $contact = $this->convertToCamelCase($data);
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
            'emails'     => [],
        );
        // fill default values
        $contact = array_merge($contactFieldNames, $contact);

        foreach ($contact['addresses'] as $key => $address) {
            $bapAddress = $this->getBapAddressData(
                $address,
                array(
                    'firstName' => $contact['firstName'],
                    'lastName' => $contact['lastName']
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

        $contact['emails'][] = $contact['email'];
        unset($contact['email']);

        return $contact;
    }

    /**
     * Get BAP address data based on magento address data.
     *
     * @param array $address
     * @param array $defaultValues
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

    /**
     * Used in import
     *
     * @param mixed  $data
     * @param string $type
     * @param null   $format
     *
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type == MagentoConnectorInterface::CUSTOMER_TYPE;
    }
}
