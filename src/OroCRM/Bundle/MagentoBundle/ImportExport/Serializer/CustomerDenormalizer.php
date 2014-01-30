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
    protected $importFieldsMap
        = [
            'customer_id' => 'origin_id',
            'firstname'   => 'first_name',
            'lastname'    => 'last_name',
            'middlename'  => 'middle_name',
            'prefix'      => 'name_prefix',
            'suffix'      => 'name_suffix',
            'dob'         => 'birthday',
            'taxvat'      => 'vat',
        ];

    static protected $objectFields
        = [
            'store',
            'website',
            'group',
            'addresses',
            'updatedAt',
            'createdAt',
            'birthday'
        ];

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
     * @param $data
     *
     * @return array
     */
    protected function formatAccountData($data)
    {
        /**
         * @TODO FIXME move to converter
         */

        $account = [];

        $account['name'] = sprintf("%s %s", $data['first_name'], $data['last_name']);

        foreach ($data['addresses'] as $address) {
            $types = [];
            if (!empty($address['is_default_shipping'])) {
                $types[] = AddressType::TYPE_SHIPPING . '_address';
            }
            if (!empty($address['is_default_billing'])) {
                $types[] = AddressType::TYPE_BILLING . '_address';
            }

            foreach ($types as $type) {
                $account[$type]['namePrefix'] = $address['prefix'];
                $account[$type]['firstName'] = $address['firstname'];
                $account[$type]['middleName'] = $address['middlename'];
                $account[$type]['lastName']  = $address['lastname'];
                $account[$type]['nameSuffix'] = $address['suffix'];
                $account[$type]['organization'] = $address['company'];
                list($account[$type]['street'], $account[$type]['street2']) = explode("\n", $address['street']);
                $account[$type]['city']      = $address['city'];

                $account[$type]['postalCode'] = $address['postcode'];
                $account[$type]['country']    = $address['country_id'];
                $account[$type]['regionText'] = isset($address['region']) ? $address['region'] : null;
                $account[$type]['region']     = isset($address['region_id']) ? $address['region_id'] : null;
                $account[$type]['created']    = $address['created_at'];
                $account[$type]['updated']    = $address['updated_at'];
            }
        }

        return $account;
    }

    /**
     * @param $data
     *
     * @return array
     */
    protected function formatContactData($data)
    {
        /**
         * @TODO FIXME move to converter
         */

        $contact           = $this->convertToCamelCase($data);
        $contactFieldNames = [
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
        ];
        // fill default values
        $contact = array_merge($contactFieldNames, $contact);

        $addressFields    = [
            'firstName'  => null,
            'lastName'   => null,
            'middleName' => null,
            'namePrefix' => null,
            'nameSuffix' => null,
            'organization' => null,
            'postalCode' => null,
            'country'    => null,
            'region'     => null,
            'regionText' => null,
            'created'    => null,
            'updated'    => null,
            'types'      => []
        ];
        $addressFieldsMap = [
            'company'   => 'organization',
            'region'    => 'regionText',
            'regionId'  => 'region',
            'countryId' => 'country',
            'createdAt' => 'created',
            'updatedAt' => 'updated',
            'postcode'  => 'postalCode'
        ];
        $namesMap         = [
            'firstname'  => 'first_name',
            'lastname'   => 'last_name',
            'middlename' => 'middle_name',
            'prefix'     => 'name_prefix',
            'suffix'     => 'name_suffix',
        ];

        foreach ($contact['addresses'] as $key => $address) {
            // process keys that will not be camelized correctly
            $address = $this->mapParams($namesMap, $address);
            $address = $this->convertToCamelCase($address);
            $address = $this->mapParams($addressFieldsMap, $address);
            $address = array_merge($addressFields, $address);
            //merge firstName and lastName from contact in case when it's not filled in address
            $address = array_merge(array_intersect_key(array_flip(['firstName', 'lastName']), $contact), $address);

            // prepare address types
            if (!empty($address['isDefaultShipping'])) {
                $address['types'][] = AddressType::TYPE_SHIPPING;
            }
            if (!empty($address['isDefaultBilling'])) {
                $address['types'][] = AddressType::TYPE_BILLING;
            }
            list($address['street'], $address['street2']) = explode("\n", $address['street']);

            if (!empty($address['telephone'])
                && !in_array($address['telephone'], $contact['phones'])
            ) {
                $contact['phones'][] = $address['telephone'];
            }
            $contact['addresses'][$key] = $address;
        }

        $contact['emails'][] = $contact['email'];
        unset($contact['email']);

        return $contact;
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

    /**
     * Process params mapping
     *
     * @param array $map
     * @param array $params
     *
     * @return array
     */
    protected function mapParams($map, $params)
    {
        $keys = [];
        foreach (array_keys($params) as $key) {
            if (isset($map[$key])) {
                $keys[] = $map[$key];
            } else {
                $keys[] = $key;
            }
        }

        return array_combine($keys, array_values($params));
    }
}
