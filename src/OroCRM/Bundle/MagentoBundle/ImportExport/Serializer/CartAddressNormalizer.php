<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\Normalizer\CompositeNormalizer;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;

class CartAddressNormalizer extends CompositeNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return MagentoConnectorInterface::CART_ADDRESS_TYPE == $type;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof CartAddress;
    }
}
