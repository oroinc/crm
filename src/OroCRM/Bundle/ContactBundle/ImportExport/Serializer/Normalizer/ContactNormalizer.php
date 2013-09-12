<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use OroCRM\Bundle\ContactBundle\Formatter\SocialUrlFormatter;
use OroCRM\Bundle\ContactBundle\Model\Social;

use OroCRM\Bundle\ContactBundle\Entity\Contact;

class ContactNormalizer implements NormalizerInterface, DenormalizerInterface
{
    const CONTACT_TYPE = 'OroCRM\Bundle\ContactBundle\Entity\Contact';

    static protected $scalarFields = array(
        'id',
        'namePrefix',
        'firstName',
        'lastName',
        'nameSuffix',
        'gender',
        'birthday',
        'description',
        'jobTitle',
        'fax',
        'skype',
        'twitter',
        'facebook',
        'googlePlus',
        'linkedIn',
    );

    static protected $socialFields = array(
        Social::TWITTER => 'twitter',
        Social::FACEBOOK => 'facebook',
        Social::GOOGLE_PLUS => 'googlePlus',
        Social::LINKED_IN => 'linkedIn',
    );

    /**
     * @var SocialUrlFormatter
     */
    protected $socialUrlFormatter;

    public function __construct(SocialUrlFormatter $socialUrlFormatter)
    {
        $this->socialUrlFormatter = $socialUrlFormatter;
    }

    /**
     * @param Contact $object
     * @param mixed $format
     * @param array $context
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return $this->getScalarFieldsValues($object);
    }

    /**
     * @param Contact $object
     * @return array
     */
    protected function getScalarFieldsValues(Contact $object)
    {
        $result = array();
        foreach (static::$scalarFields as $fieldName) {
            $getter = 'get' .ucfirst($fieldName);
            $result[$fieldName] = $object->$getter();
        }
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
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return Contact
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $result = new Contact();
        $this->setScalarFieldsValues($result, $data);
        return $result;
    }

    /**
     * @param Contact $object
     * @param array $data
     */
    protected function setScalarFieldsValues(Contact $object, array $data)
    {
        foreach (static::$scalarFields as $fieldName) {
            $setter = 'set' .ucfirst($fieldName);
            if (array_key_exists($fieldName, $data)) {
                $object->$setter($data[$fieldName]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Contact;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type = self::CONTACT_TYPE;
    }
}
