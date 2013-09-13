<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer;

use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use OroCRM\Bundle\ContactBundle\Formatter\SocialUrlFormatter;
use OroCRM\Bundle\ContactBundle\Model\Social;

use OroCRM\Bundle\ContactBundle\Entity\Contact;

class ContactNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    const CONTACT_TYPE = 'OroCRM\Bundle\ContactBundle\Entity\Contact';
    const SOURCE_TYPE = 'OroCRM\Bundle\ContactBundle\Entity\Source';
    const METHOD_TYPE = 'OroCRM\Bundle\ContactBundle\Entity\Method';
    const USER_TYPE = 'Oro\Bundle\UserBundle\Entity\User';

    static protected $scalarFields = array(
        'id',
        'namePrefix',
        'firstName',
        'lastName',
        'nameSuffix',
        'gender',
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

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function setSocialUrlFormatter(SocialUrlFormatter $socialUrlFormatter)
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
        $result = $this->getScalarFieldsValues($object);

        $result['birthday'] = $this->normalizeObject($object->getBirthday(), $format, $context);
        $result['source'] = $this->normalizeObject($object->getSource(), $format, $context);
        $result['method'] = $this->normalizeObject($object->getMethod(), $format, $context);
        $result['owner'] = $this->normalizeObject(
            $object->getOwner(),
            $format,
            array_merge($context, array('mode' => 'short'))
        );

        return $result;
    }

    /**
     * @param mixed $object
     * @param mixed $format
     * @param array $context
     * @return mixed
     */
    protected function normalizeObject($object, $format = null, array $context = array())
    {
        $result = null;
        if (is_object($object)) {
            $result = $this->serializer->serialize($object, $format, $context);
        }
        return $result;
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
        $data = is_array($data) ? $data : array();
        $result = new Contact();
        $this->setScalarFieldsValues($result, $data);

        $birthday = $this->denormalizeObject($data, 'birthday', 'DateTime', $format, $context);
        if ($birthday) {
            $result->setBirthday($birthday);
        }

        $source = $this->denormalizeObject($data, 'source', static::SOURCE_TYPE, $format, $context);
        if ($source) {
            $result->setSource($source);
        }

        $method = $this->denormalizeObject($data, 'method', static::METHOD_TYPE, $format, $context);
        if ($method) {
            $result->setMethod($method);
        }

        $owner = $this->denormalizeObject(
            $data,
            'owner',
            static::USER_TYPE,
            $format,
            array_merge($context, array('mode' => 'short'))
        );
        if ($owner) {
            $result->setOwner($owner);
        }

        return $result;
    }

    /**
     * @param array $data
     * @param string $name
     * @param string $type
     * @param mixed $format
     * @param array $context
     * @return null|object
     */
    protected function denormalizeObject(array $data, $name, $type, $format = null, $context = array())
    {
        $result = null;
        if (!empty($data[$name])) {
            $result = $this->serializer->deserialize($data[$name], $type, $format, $context);

        }
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
        return is_array($data) && $type == static::CONTACT_TYPE;
    }
}
