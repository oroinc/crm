<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\TypedAddressNormalizer;

class MagentoAddressNormalizer extends TypedAddressNormalizer
{
    const ADDRESS_TYPE = 'OroCRM\Bundle\MagentoBundle\Entity\Address';

    /**
     * @param mixed  $data
     * @param string $class
     * @param mixed  $format
     * @param array  $context
     *
     * @return TypedAddressNormalizer
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $result = parent::denormalize($data, $class, $format, $context);

        // can be empty when using this normalizer with cart
        if (!empty($data['customerAddressId'])) {
            $result->setId($data['customerAddressId']);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && class_exists($type) && static::ADDRESS_TYPE == $type;
    }
}
