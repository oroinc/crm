<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\Normalizer;

use OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactNormalizer as BaceNormalizer;

class ContactNormalizer extends BaceNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof Contact && strpos($context['processorAlias'], 'orocrm_magento') !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return is_array($data) && $type == static::CONTACT_TYPE
            && strpos($context['processorAlias'], 'orocrm_magento') !== false;
    }
}
