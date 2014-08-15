<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use OroCRM\Bundle\MagentoBundle\Entity\OrderAddress;
use OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\Normalizer\CompositeNormalizer;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

class OrderAddressCompositeDenormalizer extends CompositeNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return MagentoConnectorInterface::ORDER_ADDRESS_TYPE === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof OrderAddress;
    }
}
