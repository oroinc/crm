<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use OroCRM\Bundle\ContactBundle\Entity\Group;

class GroupNormalizer implements NormalizerInterface, DenormalizerInterface
{
    const GROUP_TYPE = 'OroCRM\Bundle\ContactBundle\Entity\Group';

    /**
     * @param Group $object
     * @param mixed $format
     * @param array $context
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return $object->getLabel();
    }

    /**
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return Group
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        return new Group($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Group;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_string($data) && $type == static::GROUP_TYPE;
    }
}
