<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\AddressBundle\Entity\AddressType;

use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\AccountBundle\ImportExport\Serializer\Normalizer\AccountNormalizer;
use OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactNormalizer;

class CustomerNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    protected $importFieldsMap = [
        'customer_id' => 'original_id',
        'firstname'   => 'first_name',
        'lastname'    => 'last_name',
        'middlename'  => 'middle_name',
        'prefix'      => 'name_prefix',
        'suffix'      => 'name_suffix',
        'dob'         => 'birthday',
        'taxvat'      => 'vat',
    ];

    static protected $objectFields = array(
        'store',
        'website',
        'group',
        'addresses',
        'updatedAt',
        'createdAt',
        'birthday',
    );

    const STORE_TYPE          = 'OroCRM\Bundle\MagentoBundle\Entity\Store';
    const WEBSITE_TYPE        = 'OroCRM\Bundle\MagentoBundle\Entity\Website';
    const GROUPS_TYPE         = 'OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup';
    const ADDRESSES_TYPE      = 'ArrayCollection<OroCRM\Bundle\ContactBundle\Entity\ContactAddress>';
    const MAGE_ADDRESSES_TYPE = 'ArrayCollection<OroCRM\Bundle\MagentoBundle\Entity\Address>';

    /**
     * @var SerializerInterface|NormalizerInterface|DenormalizerInterface
     */
    protected $serializer;

    public function setSerializer(SerializerInterface $serializer)
    {
        if (!$serializer instanceof NormalizerInterface || !$serializer instanceof DenormalizerInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Serializer must implement "%s" and "%s"',
                    'Symfony\Component\Serializer\Normalizer\NormalizerInterface',
                    'Symfony\Component\Serializer\Normalizer\DenormalizerInterface'
                )
            );
        }
        $this->serializer = $serializer;
    }

    /**
     * For exporting customers
     *
     * @param Customer $object
     * @param null     $format
     * @param array    $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        /*
         * TODO: consider automatically converting to array
         * (public props, or toArray method, or add all required fields here)
         */
        if (method_exists($object, 'toArray')) {
            $result = $object->toArray($format, $context);
        } else {
            $result = array(
                'customer_id' => $object->getId(),
                'firstname'   => $object->getFirstName(),
                'lastname'    => $object->getLastName(),
                'email'       => $object->getEmail(),
                'store_id'    => $object->getStore()->getId(),
                'website_id'  => $object->getWebsite()->getId(),
                // TODO: continue with other fields, use $importFieldsMap
            );
        }

        return $result;
    }

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
        $data         = is_array($data) ? $data : [];
        $resultObject = new Customer();

        $mappedData = [];
        foreach ($data as $key => $value) {
            $fieldKey              = isset($this->importFieldsMap[$key]) ? $this->importFieldsMap[$key] : $key;
            $mappedData[$fieldKey] = $value;
        }

        if (!empty($mappedData['birthday'])) {
            $mappedData['birthday'] = substr($mappedData['birthday'], 0, 10);
        }

        $this->setScalarFieldsValues($resultObject, $mappedData);
        $this->setObjectFieldsValues($resultObject, $mappedData);

        $resultObject->setChannel($context['channel']);

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
     * Convert assoc array with 'sample_key' keys notation
     * to camel case 'sampleKey'
     *
     * @param array $data
     *
     * @return array
     */
    protected function convertToCamelCase($data)
    {
        $result = [];
        foreach ($data as $itemName => $item) {
            $fieldName = preg_replace_callback(
                '/_([a-z])/',
                function ($string) {
                    return strtoupper($string[1]);
                },
                $itemName
            );

            $result[$fieldName] = $item;
        }

        return $result;
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
        $contact = $this->denormalizeObject($data, 'contact', ContactNormalizer::CONTACT_TYPE, $format, $context);

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
        $website = $this->denormalizeObject($data, 'website', static::WEBSITE_TYPE, $format, $context);

        /** @var Store $store */
        $store = $this->denormalizeObject($data, 'store', static::STORE_TYPE, $format, $context);
        $store->setWebsite($website);

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

        $object
            ->setWebsite($website)
            ->setStore($store)
            ->setGroup($this->denormalizeObject($data, 'group', static::GROUPS_TYPE, $format, $context))
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

        $addresses = $this->denormalizeObject($data, 'addresses', static::MAGE_ADDRESSES_TYPE, $format, $context);
        if (!empty($addresses)) {
            $object->resetAddresses($addresses);
        }
    }

    /**
     * @param       $collection
     * @param mixed $format
     * @param array $context
     *
     * @return mixed
     */
    protected function normalizeCollection($collection, $format = null, array $context = array())
    {
        $result = array();
        if ($collection instanceof Collection && !$collection->isEmpty()) {
            $result = $this->serializer->normalize($collection, $format, $context);
        }

        return $result;
    }


    /**
     * @param $data
     *
     * @return array
     */
    protected function formatAccountData($data)
    {
        $account = [];

        $account['name'] = sprintf("%s %s", $data['first_name'], $data['last_name']);

        foreach ($data['addresses'] as $address) {
            $type = false;

            // prepare address types
            if (!empty($address['is_default_shipping'])) {
                $type = AddressType::TYPE_SHIPPING . '_address';
            }

            if (!empty($address['is_default_billing'])) {
                $type = AddressType::TYPE_BILLING . '_address';
            }

            if ($type) {
                $account[$type]['firstName'] = $address['firstname'];
                $account[$type]['lastName']  = $address['lastname'];
                $account[$type]['street']    = $address['street'];
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
            'postalCode' => null,
            'country'    => null,
            'region'     => null,
            'regionText' => null,
            'created'    => null,
            'updated'    => null,
            'types'      => []
        ];
        $addressFieldsMap = [
            'region'     => 'regionText',
            'region_id'  => 'region',
            'country_id' => 'country',
            'created_at' => 'created',
            'updated_at' => 'updated',
            'postcode'   => 'postalCode'
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
                $address['types'][] = 'shipping';
            }
            if (!empty($address['isDefaultBilling'])) {
                $address['types'][] = 'billing';
            }

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
     * Used in export
     *
     * @param mixed $data
     * @param null  $format
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Customer;
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
        return is_array($data) && $type == 'OroCRM\Bundle\MagentoBundle\Entity\Customer';
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
