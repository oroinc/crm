<?php

namespace Oro\Bundle\ContactBundle\ImportExport\Serializer\Normalizer;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Formatter\SocialUrlFormatter;
use Oro\Bundle\ContactBundle\Model\Social;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ContactNormalizer extends ConfigurableEntityNormalizer
{
    const CONTACT_TYPE = 'Oro\Bundle\ContactBundle\Entity\Contact';

    /**
     * @var array
     */
    protected static $socialFields = array(
        Social::TWITTER     => 'twitter',
        Social::FACEBOOK    => 'facebook',
        Social::GOOGLE_PLUS => 'googlePlus',
        Social::LINKED_IN   => 'linkedIn',
    );

    /**
     * @var SocialUrlFormatter
     */
    protected $socialUrlFormatter;

    /**
     * @var SerializerInterface|NormalizerInterface|DenormalizerInterface
     */
    protected $serializer;

    public function setSocialUrlFormatter(SocialUrlFormatter $socialUrlFormatter)
    {
        $this->socialUrlFormatter = $socialUrlFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $result = parent::normalize($object, $format, $context);

        foreach (static::$socialFields as $socialType => $fieldName) {
            if (!empty($result[$fieldName])) {
                $result[$fieldName] = $this->socialUrlFormatter->getSocialUrl(
                    $socialType,
                    $result[$fieldName]
                );
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        foreach (static::$socialFields as $socialType => $fieldName) {
            if (!empty($data[$fieldName])) {
                $data[$fieldName] = $this->socialUrlFormatter->getSocialUsername(
                    $socialType,
                    $data[$fieldName]
                );
            }
        }

        return parent::denormalize($data, $class, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return $data instanceof Contact;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return is_array($data) && $type == static::CONTACT_TYPE;
    }
}
