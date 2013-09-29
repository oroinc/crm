<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use OroCRM\Bundle\ContactBundle\Entity\Method;

class MethodNormalizer implements NormalizerInterface, DenormalizerInterface
{
    const METHOD_TYPE = 'OroCRM\Bundle\ContactBundle\Entity\Method';

    /**
     * @param Method $object
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
     * @return Method
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        return new Method($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Method;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_string($data) && $type == static::METHOD_TYPE;
    }
}
