<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;

class ContactPhoneNormalizer implements NormalizerInterface, DenormalizerInterface
{
    const CONTACT_PHONE_TYPE = 'OroCRM\Bundle\ContactBundle\Entity\ContactPhone';

    /**
     * @param ContactPhone $object
     * @param mixed $format
     * @param array $context
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return $object->getPhone();
    }

    /**
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return ContactPhone
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $result = new ContactPhone();
        $result->setPhone($data);
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ContactPhone;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_string($data) && $type == static::CONTACT_PHONE_TYPE;
    }
}
