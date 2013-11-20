<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Store;

class StoreNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @param Store $object
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
     * @return Store
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        /** @var Store $result */
        $result = new $class();
        $result->setId($data);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Store;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_int($data) && class_exists($type);
    }
}
