<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\Normalizer;

use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\AddressNormalizer as BaseNormalizer;

class AddressNormalizer extends BaseNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return ($data instanceof AbstractAddress) && strpos($context['processorAlias'], 'orocrm_magento') !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return is_array($data)
            && class_exists($type)
            && in_array(self::ABSTRACT_ADDRESS_TYPE, class_parents($type))
            && strpos($context['processorAlias'], 'orocrm_magento') !== false;
    }
}
