<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\Entity\Store;

class RelationNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param Store|Website|CustomerGroup $object
     * @param mixed $format
     * @param array $context
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return $object->getId();
    }

    /**
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return Store|Website
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        /** @var Store|Website|CustomerGroup $result */
        $result = new $class();
        $result->setId($data);

        if (!empty($context['data']['group_name']) && $class == CustomerNormalizer::GROUPS_TYPE) {
            $result->setName($context['data']['group_name']);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Store || $data instanceof Website;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        $supportedEntities = [
            CustomerNormalizer::STORE_TYPE,
            CustomerNormalizer::WEBSITE_TYPE,
            CustomerNormalizer::GROUPS_TYPE
        ];

        return is_int($data) && class_exists($type) && in_array($type, $supportedEntities);
    }
}
