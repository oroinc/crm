<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\TypedAddressNormalizer;

use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

class MagentoAddressNormalizer extends TypedAddressNormalizer
{
    /**
     * {@inheritdoc}
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
        return MagentoConnectorInterface::CUSTOMER_ADDRESS_TYPE == $type;
    }
}
