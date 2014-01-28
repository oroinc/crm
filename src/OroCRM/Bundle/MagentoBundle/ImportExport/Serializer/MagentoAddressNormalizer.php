<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\TypedAddressNormalizer;

use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

class MagentoAddressNormalizer extends TypedAddressNormalizer
{
    /**
     * @param mixed  $data
     * @param string $class
     * @param mixed  $format
     * @param array  $context
     *
     * @return AbstractTypedAddress
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
        return is_array($data) && class_exists($type) && MagentoConnectorInterface::CUSTOMER_ADDRESS_TYPE == $type;
    }
}
