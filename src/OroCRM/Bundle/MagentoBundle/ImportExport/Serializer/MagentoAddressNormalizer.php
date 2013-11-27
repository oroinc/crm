<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\TypedAddressNormalizer;

class MagentoAddressNormalizer extends TypedAddressNormalizer
{
    /**
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return TypedAddressNormalizer
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $result = parent::denormalize($data, $class, $format, $context);
        $result->setId($data['customer_address_id']);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            is_array($data)
            && class_exists($type)
            && in_array(static::ABSTRACT_TYPED_ADDRESS_TYPE, class_parents($type));
    }
}
