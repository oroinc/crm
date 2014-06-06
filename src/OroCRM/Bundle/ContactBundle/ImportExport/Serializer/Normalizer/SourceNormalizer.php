<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\NormalizerInterface;
use OroCRM\Bundle\ContactBundle\Entity\Source;

class SourceNormalizer implements NormalizerInterface, DenormalizerInterface
{
    const SOURCE_TYPE = 'OroCRM\Bundle\ContactBundle\Entity\Source';

    /**
     * @param Source $object
     * @param mixed $format
     * @param array $context
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return $object->getName();
    }

    /**
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return Source
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        return new Source($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof Source;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return is_string($data) && $type == static::SOURCE_TYPE;
    }
}
