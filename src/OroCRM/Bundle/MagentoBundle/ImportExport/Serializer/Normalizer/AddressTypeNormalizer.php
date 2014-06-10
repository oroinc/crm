<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\Normalizer;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\AddressTypeNormalizer as BaseNormalizer;

class AddressTypeNormalizer extends BaseNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return ($data instanceof AddressType) && strpos($context['processorAlias'], 'orocrm_magento') !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return is_string($data) && $type == self::ADDRESS_TYPE_TYPE
            && strpos($context['processorAlias'], 'orocrm_magento') !== false;
    }
}
