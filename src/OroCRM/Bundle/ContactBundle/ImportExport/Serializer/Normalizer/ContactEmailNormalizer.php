<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;

class ContactEmailNormalizer implements NormalizerInterface, DenormalizerInterface
{
    const CONTACT_EMAIL_TYPE = 'OroCRM\Bundle\ContactBundle\Entity\ContactEmail';

    /**
     * @param ContactEmail $object
     * @param mixed $format
     * @param array $context
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return $object->getEmail();
    }

    /**
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return ContactEmail
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $result = new ContactEmail();
        $result->setEmail($data);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ContactEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_string($data) && $type == static::CONTACT_EMAIL_TYPE;
    }
}
