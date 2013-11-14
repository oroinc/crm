<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CustomerNormalizer implements NormalizerInterface, DenormalizerInterface
{
    protected $importFieldsMap = [
        'customer_id' => 'id',
        'firstname'   => 'first_name',
        'lastname'    => 'last_name',
        'middlename'  => 'middle_name',
        'prefix'      => 'name_prefix',
        'suffix'      => 'name_suffix',
    ];

    static protected $objectFields = array(
        'store_id',
        'website_id',
        'group_id',
        'addresses',
    );

    const STORES_TYPE    = 'ArrayCollection<OroCRM\Bundle\MagentoBundle\Entity\Store>';
    const WEBSITES_TYPE  = 'ArrayCollection<OroCRM\Bundle\MagentoBundle\Entity\Website>';
    const GROUPS_TYPE    = 'ArrayCollection<OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup>';
    const ADDRESSES_TYPE = 'ArrayCollection<OroCRM\Bundle\ContactBundle\Entity\ContactAddress>';

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
        $data = is_array($data) ? $data : array();
        $resultObject = new Customer();


        $mappedData = [];
        foreach ($data as $key => $value) {
            $fieldKey = isset($this->importFieldsMap[$key]) ? $this->importFieldsMap[$key] : $key;
            $mappedData[$fieldKey] = $value;

//            switch ($key) {
//                case 'store_id':
//                case 'website_id':
//                case 'group_id':
//                    $id = $value;
//                    $value = $this->initRelatedObject($key);
//                    $value->setId($id)
//                          ->setName($data[str_replace('_id', '', $key)])
//                          ->setCode(strtolower(str_replace(' ', '', $key)));
//                    break;
//                case 'addresses':
//                    break;
//                default:
//                    $fields[$fieldKey] = $value;
//                    break;
//            }
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
        $object->fillFromArray($data);
    }

    /**
     * @param Customer $object
     * @param array $data
     * @param mixed $format
     * @param array $context
     */
    protected function setObjectFieldsValues(Customer $object, array $data, $format = null, array $context = array())
    {
        $this->setNotEmptyValues(
            $object,
            [
                [
                    'name' => 'store',
                    'value' => $this->denormalizeObject($data, 'store', static::STORES_TYPE, $format, $context)
                ],
                [
                    'name' => 'website',
                    'value' => $this->denormalizeObject($data, 'website', static::WEBSITES_TYPE, $format, $context)
                ],
                [
                    'name' => 'group',
                    'value' => $this->denormalizeObject($data, 'group', static::GROUPS_TYPE, $format, $context)
                ],
            ]
        );

        // TODO: normalize and set addresses to contact and bill/ship addr to account
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
     * @param Customer $object
     * @param array $valuesData
     */
    protected function setNotEmptyValues(Customer $object, array $valuesData)
    {
        foreach ($valuesData as $data) {
            $value = $data['value'];
            if (!$value) {
                continue;
            }
            if (isset($data['name'])) {
                $setter = 'set' . ucfirst($data['name']);
                $object->$setter($value);
            } elseif (isset($data['setter'])) {
                $setter = $data['setter'];
                $object->$setter($value);
            } elseif (is_array($value) || $value instanceof \Traversable) {
                $adder = $data['adder'];
                foreach ($value as $element) {
                    $object->$adder($element);
                }
            }
        }
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
