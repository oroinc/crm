<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\Normalizer;

use OroCRM\Bundle\AccountBundle\ImportExport\Serializer\Normalizer\AccountNormalizer as BaseNormalizer;

class AccountNormalizer extends BaseNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof Account && strpos($context['processorAlias'], 'orocrm_magento') !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return ((is_array($data) || is_string($data)) && $type == static::ACCOUNT_TYPE)
            && strpos($context['processorAlias'], 'orocrm_magento') !== false;
    }
}
