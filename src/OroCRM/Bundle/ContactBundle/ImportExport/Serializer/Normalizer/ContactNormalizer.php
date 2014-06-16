<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\ConfigurableEntityNormalizer;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Formatter\SocialUrlFormatter;
use OroCRM\Bundle\ContactBundle\Model\Social;

class ContactNormalizer extends ConfigurableEntityNormalizer
{
    const CONTACT_TYPE = 'OroCRM\Bundle\ContactBundle\Entity\Contact';

    /**
     * @var array
     */
    static protected $socialFields = array(
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

    /**
     * @param SocialUrlFormatter $socialUrlFormatter
     */
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
