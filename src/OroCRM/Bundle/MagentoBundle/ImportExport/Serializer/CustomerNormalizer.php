<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\AccountBundle\ImportExport\Serializer\Normalizer\AccountNormalizer;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CustomerNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    protected $importFieldsMap = [
        'customer_id' => 'original_id',
        'firstname'   => 'first_name',
        'lastname'    => 'last_name',
        'middlename'  => 'middle_name',
        'prefix'      => 'name_prefix',
        'suffix'      => 'name_suffix',
    ];

    static protected $objectFields = array(
        'store',
        'website',
        'group',
        'addresses',
    );

    const STORE_TYPE     = 'OroCRM\Bundle\MagentoBundle\Entity\Store';
    const WEBSITE_TYPE   = 'OroCRM\Bundle\MagentoBundle\Entity\Website';
    const GROUPS_TYPE    = 'OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup';
    const ADDRESSES_TYPE = 'ArrayCollection<OroCRM\Bundle\ContactBundle\Entity\ContactAddress>';

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
     * @param null $format
     * @param array $context
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
     * @param mixed $data
     * @param string $class
     * @param null $format
     * @param array $context
     * @return object|Customer
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $data = is_array($data) ? $data : [];
        $resultObject = new Customer();

        $mappedData = [];
        foreach ($data as $key => $value) {
            $fieldKey = isset($this->importFieldsMap[$key]) ? $this->importFieldsMap[$key] : $key;
            $mappedData[$fieldKey] = $value;
        }

        $this->setScalarFieldsValues($resultObject, $mappedData);
        $this->setObjectFieldsValues($resultObject, $mappedData);

        return $resultObject;
    }

    /**
     * @param Customer $object
     * @param array $data
     */
    protected function setScalarFieldsValues(Customer $object, array $data)
    {
        foreach (['created_at', 'updated_at'] as $itemName) {
            if (isset($data[$itemName]) && is_string($data[$itemName])) {
                $timezone = new \DateTimeZone('UTC');
                $data[$itemName] = new \DateTime($data[$itemName], $timezone);
            }
        }

        $data = $this->convertToCamelCase($data);
        foreach ($data as $itemName => $item) {
            $method = 'set' . ucfirst($itemName);

            if (method_exists($object, $method) && !in_array($itemName, self::$objectFields)) {
                $object->$method($item);
            }
        }
    }

    /**
     * Convert assoc array with 'sample_key' keys notation
     * to camel case 'sampleKey'
     *
     * @param array $data
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
     * @param array $data
     * @param mixed $format
     * @param array $context
     */
    protected function setObjectFieldsValues(Customer $object, array $data, $format = null, array $context = array())
    {
        // format contact data
        $data['contact'] = $this->formatContactData($data);

        // format account data
        $data['account'] = $data['contact']['firstName'] . ' ' . $data['contact']['lastName'];

        /** @var Contact $contact */
        $contact = $this->denormalizeObject($data, 'contact', ContactNormalizer::CONTACT_TYPE, $format, $context);

        /** @var Account $account */
        $account = $this->denormalizeObject(
            $data,
            'account',
            AccountNormalizer::ACCOUNT_TYPE,
            $format,
            array_merge($context, ['mode' => AccountNormalizer::SHORT_MODE])
        );

        /** @var Website $website */
        $website = $this->denormalizeObject($data, 'website_id', static::WEBSITE_TYPE, $format, $context);

        /** @var Store $store */
        $store = $this->denormalizeObject($data, 'store_id', static::STORE_TYPE, $format, $context);
        $store->setWebsite($website);

        $object
            ->setWebsite($website)
            ->setStore($store)
            ->setGroup(
                $this->denormalizeObject(
                    $data,
                    'group_id',
                    static::GROUPS_TYPE,
                    $format,
                    array_merge($context, ['data' => $data])
                )
            )
            ->setContact($contact)
            ->setAccount($account);
    }

    /**
     * @param $data
     * @return array
     */
    protected function formatContactData($data)
    {
        $contact = [];

        $contactData = $this->convertToCamelCase($data);
        $contactFieldNames = [
            'firstName',
            'lastName',
            'middleName',
            'namePrefix',
            'nameSuffix',
            'gender',
            'addresses',
            'birthday'
        ];

        // format contact data
        foreach ($contactFieldNames as $fieldName) {
            $contact[$fieldName] = empty($contactData[$fieldName]) ? null : $contactData[$fieldName];
        }

        // format contact addresses data
        foreach ($contact['addresses'] as $key => $address) {
            $contact['addresses'][$key] = array_merge($contact['addresses'][$key], $contact);

            // TODO: make sure this works after CRM-185
            // TODO: test this after we'll have Magento region db in place
            $contact['addresses'][$key]['postalCode'] = $contact['addresses'][$key]['postcode'];
            $contact['addresses'][$key]['country']    = $contact['addresses'][$key]['country_id'];
            $contact['addresses'][$key]['regionText'] = $contact['addresses'][$key]['region'];
            $contact['addresses'][$key]['region']     = $contact['addresses'][$key]['region_id'];

            // TODO: make sure datetime normalized and set correctly to object
            $contact['addresses'][$key]['created']     = $contact['addresses'][$key]['created_at'];
            $contact['addresses'][$key]['updated']     = $contact['addresses'][$key]['updated_at'];
        }

        return $contact;
    }

    /**
     * @param array $data
     * @param string $name
     * @param string $type
     * @param mixed $format
     * @param array $context
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

    protected function initRelatedObject($type)
    {
        // TODO: find or create
        if ($type == 'store_id') {
            return new Store();
        }

        if ($type == 'website_id') {
            return new Website();
        }

        if ($type == 'group_id') {
            return new CustomerGroup();
        }
    }

    /**
     * Used in export
     *
     * @param mixed $data
     * @param null $format
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Customer;
    }

    /**
     * Used in import
     *
     * @param mixed $data
     * @param string $type
     * @param null $format
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type == 'OroCRM\Bundle\MagentoBundle\Entity\Customer';
    }
}
