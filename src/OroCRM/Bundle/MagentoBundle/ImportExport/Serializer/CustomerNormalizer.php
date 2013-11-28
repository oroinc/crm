<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

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
        'updated_at',
        'updatedAt',
        'created_at',
        'createdAt',
        'birthday',
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

        $mappedData['birthday'] = substr($mappedData['birthday'], 0, 10);

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
        $data = $this->convertToCamelCase($data);
        foreach ($data as $itemName => $item) {
            if (in_array($itemName, static::$objectFields)) {
                continue;
            }

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
        $contact['addresses'] = empty($contact['addresses']) ? [] : $contact['addresses'];
        foreach ($contact['addresses'] as $key => $address) {
            // fill address with contact info
            $contact['addresses'][$key] = array_merge($contact['addresses'][$key], $contact);
            unset($contact['addresses'][$key]['addresses']);

            // TODO: make sure this works after CRM-185
            $contact['addresses'][$key]['postalCode'] = $contact['addresses'][$key]['postcode'];
            $contact['addresses'][$key]['country']    = $contact['addresses'][$key]['country_id'];
            $contact['addresses'][$key]['regionText'] = $contact['addresses'][$key]['region'];
            $contact['addresses'][$key]['region']     = $contact['addresses'][$key]['region_id'];

            // TODO: make sure datetime normalized and set correctly to object
            $contact['addresses'][$key]['created']     = $contact['addresses'][$key]['created_at'];
            $contact['addresses'][$key]['updated']     = $contact['addresses'][$key]['updated_at'];

            // prepare address types
            if (!empty($contact['addresses'][$key]['is_default_shipping'])) {
                $contact['addresses'][$key]['types'][] = 'shipping';
            }
            if (!empty($contact['addresses'][$key]['is_default_billing'])) {
                $contact['addresses'][$key]['types'][] = 'billing';
            }
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
