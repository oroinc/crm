<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer;

use Doctrine\Common\Collections\Collection;

use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

use OroCRM\Bundle\ContactBundle\Formatter\SocialUrlFormatter;
use OroCRM\Bundle\ContactBundle\Model\Social;

use OroCRM\Bundle\ContactBundle\Entity\Contact;

class ContactNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    const CONTACT_TYPE   = 'OroCRM\Bundle\ContactBundle\Entity\Contact';
    const SOURCE_TYPE    = 'OroCRM\Bundle\ContactBundle\Entity\Source';
    const METHOD_TYPE    = 'OroCRM\Bundle\ContactBundle\Entity\Method';
    const USER_TYPE      = 'Oro\Bundle\UserBundle\Entity\User';
    const EMAILS_TYPE    = 'ArrayCollection<OroCRM\Bundle\ContactBundle\Entity\ContactEmail>';
    const PHONES_TYPE    = 'ArrayCollection<OroCRM\Bundle\ContactBundle\Entity\ContactPhone>';
    const GROUPS_TYPE    = 'ArrayCollection<OroCRM\Bundle\ContactBundle\Entity\Group>';
    const ACCOUNTS_TYPE  = 'ArrayCollection<OroCRM\Bundle\AccountBundle\Entity\Account>';
    const ADDRESSES_TYPE = 'ArrayCollection<OroCRM\Bundle\ContactBundle\Entity\ContactAddress>';

    static protected $scalarFields = array(
        'id',
        'namePrefix',
        'firstName',
        'middleName',
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
     * @var SerializerInterface|NormalizerInterface|DenormalizerInterface
     */
    protected $serializer;

    public function setSerializer(SerializerInterface $serializer)
    {
        if (!$serializer instanceof NormalizerInterface || !$serializer instanceof DenormalizerInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Serializer must implement "%s" and "%s"',
                    'Symfony\Component\Serializer\Normalizer\NormalizerInterface',
                    'Symfony\Component\Serializer\Normalizer\DenormalizerInterface'
                )
            );
        }
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

        $result['birthday'] = $this->normalizeObject(
            $object->getBirthday(),
            $format,
            array_merge($context, array('type' => 'date'))
        );
        $result['source'] = $this->normalizeObject($object->getSource(), $format, $context);
        $result['method'] = $this->normalizeObject($object->getMethod(), $format, $context);
        $result['owner'] = $this->normalizeObject(
            $object->getOwner(),
            $format,
            array_merge($context, array('mode' => 'short'))
        );
        $result['assignedTo'] = $this->normalizeObject(
            $object->getAssignedTo(),
            $format,
            array_merge($context, array('mode' => 'short'))
        );
        $result['emails'] = $this->normalizeCollection($object->getEmails(), $format, $context);
        $result['phones'] = $this->normalizeCollection($object->getPhones(), $format, $context);
        $result['groups'] = $this->normalizeCollection($object->getGroups(), $format, $context);
        $result['accounts'] = $this->normalizeCollection(
            $object->getAccounts(),
            $format,
            array_merge($context, array('mode' => 'short'))
        );
        $result['addresses'] = $this->normalizeCollection(
            $object->getAddresses(),
            $format,
            $context
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
            $result = $this->serializer->normalize($object, $format, $context);
        }
        return $result;
    }

    /**
     * @param mixed $collection
     * @param mixed $format
     * @param array $context
     * @return mixed
     */
    protected function normalizeCollection($collection, $format = null, array $context = array())
    {
        $result = array();
        if ($collection instanceof Collection && !$collection->isEmpty()) {
            $result = $this->serializer->normalize($collection, $format, $context);
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
        $this->setObjectFieldsValues($result, $data);

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
     * @param Contact $object
     * @param array $data
     * @param mixed $format
     * @param array $context
     */
    protected function setObjectFieldsValues(Contact $object, array $data, $format = null, array $context = array())
    {
        $this->setNotEmptyValues(
            $object,
            array(
                array(
                    'name' => 'birthday',
                    'value' => $this->denormalizeObject(
                        $data,
                        'birthday',
                        'DateTime',
                        $format,
                        array_merge($context, array('type' => 'date'))
                    )
                ),
                array(
                    'name' => 'source',
                    'value' => $this->denormalizeObject($data, 'source', static::SOURCE_TYPE, $format, $context)
                ),
                array(
                    'name' => 'method',
                    'value' => $this->denormalizeObject($data, 'method', static::METHOD_TYPE, $format, $context)
                ),
                array(
                    'name' => 'owner',
                    'value' => $this->denormalizeObject(
                        $data,
                        'owner',
                        static::USER_TYPE,
                        $format,
                        array_merge($context, array('mode' => 'short'))
                    )
                ),
                array(
                    'name' => 'assignedTo',
                    'value' => $this->denormalizeObject(
                        $data,
                        'assignedTo',
                        static::USER_TYPE,
                        $format,
                        array_merge($context, array('mode' => 'short'))
                    )
                ),
                array(
                    'setter' => 'resetEmails',
                    'value' => $this->denormalizeObject($data, 'emails', static::EMAILS_TYPE, $format, $context)
                ),
                array(
                    'setter' => 'resetPhones',
                    'value' => $this->denormalizeObject($data, 'phones', static::PHONES_TYPE, $format, $context)
                ),
                array(
                    'adder' => 'addGroup',
                    'value' => $this->denormalizeObject($data, 'groups', static::GROUPS_TYPE, $format, $context)
                ),
                array(
                    'adder' => 'addAccount',
                    'value' => $this->denormalizeObject(
                        $data,
                        'accounts',
                        static::ACCOUNTS_TYPE,
                        $format,
                        array_merge($context, array('mode' => 'short'))
                    )
                ),
                array(
                    'adder' => 'addAddress',
                    'value' => $this->denormalizeObject($data, 'addresses', static::ADDRESSES_TYPE, $format, $context)
                ),
            )
        );
    }

    /**
     * @param Contact $object
     * @param array $valuesData
     */
    protected function setNotEmptyValues(Contact $object, array $valuesData)
    {
        foreach ($valuesData as $data) {
            $value = $data['value'];
            if (!$value) {
                continue;
            }
            if (isset($data['name'])) {
                $setter = 'set' . ucfirst($data['name']);
                $object->$setter($value);
            } elseif (isset($data['setter'])) {
                $setter = $data['setter'];
                $object->$setter($value);
            } elseif (is_array($value) || $value instanceof \Traversable) {
                $adder = $data['adder'];
                foreach ($value as $element) {
                    $object->$adder($element);
                }
            }
        }
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
            $result = $this->serializer->denormalize($data[$name], $type, $format, $context);

        }
        return $result;
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
