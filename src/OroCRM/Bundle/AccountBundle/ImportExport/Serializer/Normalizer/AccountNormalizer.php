<?php

namespace OroCRM\Bundle\AccountBundle\ImportExport\Serializer\Normalizer;

use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Intl\Exception\NotImplementedException;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\AbstractContextModeAwareNormalizer;

use OroCRM\Bundle\AccountBundle\Entity\Account;

class AccountNormalizer extends AbstractContextModeAwareNormalizer implements SerializerAwareInterface
{
    const FULL_MODE      = 'full';
    const SHORT_MODE     = 'short';
    const ACCOUNT_TYPE   = 'OroCRM\Bundle\AccountBundle\Entity\Account';
    const ADDRESSES_TYPE = 'Oro\Bundle\AddressBundle\Entity\Address';

    static protected $scalarFields = array(
        'id',
        'name',
    );

    /**
     * @var SerializerInterface|NormalizerInterface|DenormalizerInterface
     */
    protected $serializer;

    public function __construct()
    {
        parent::__construct(array(self::FULL_MODE, self::SHORT_MODE));
    }

    /**
     * @param SerializerInterface $serializer
     * @throws \Symfony\Component\Serializer\Exception\InvalidArgumentException
     */
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

    /**
     * Short mode normalization
     *
     * @param Account $object
     * @param mixed $format
     * @param array $context
     * @return string
     */
    protected function normalizeShort($object, $format = null, array $context = array())
    {
        return (string)$object->getName();
    }

    /**
     * Full mode normalization
     *
     * @param Account $object
     * @param mixed $format
     * @param array $context
     * @throws NotImplementedException
     * @return string
     */
    protected function normalizeFull($object, $format = null, array $context = array())
    {
        throw new NotImplementedException('Normalization with mode "full" is not supported.');
    }

    /**
     * Short mode denormalization
     *
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return Account
     */
    protected function denormalizeShort($data, $class, $format = null, array $context = array())
    {
        $result = new Account();
        if (is_string($data)) {
            $result->setName($data);
        }
        return $result;
    }

    /**
     * Full mode denormalization
     *
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return Account
     */
    protected function denormalizeFull($data, $class, $format = null, array $context = array())
    {
        $data = is_array($data) ? $data : array();
        $result = new Account();

        $this->setScalarFieldsValues($result, $data);
        $this->setObjectFieldsValues($result, $data);

        return $result;
    }

    /**
     * @param Account $object
     * @param array $data
     */
    protected function setScalarFieldsValues(Account $object, array $data)
    {
        foreach (static::$scalarFields as $fieldName) {
            $setter = 'set' .ucfirst($fieldName);
            if (array_key_exists($fieldName, $data)) {
                $object->$setter($data[$fieldName]);
            }
        }
    }

    /**
     * @param Account $object
     * @param array $data
     * @param mixed $format
     * @param array $context
     */
    protected function setObjectFieldsValues(Account $object, array $data, $format = null, array $context = array())
    {
        $shippingAddress = $this->denormalizeObject(
            $data,
            'shipping_address',
            static::ADDRESSES_TYPE,
            $format,
            $context
        );
        if (!empty($shippingAddress)) {
            $object->setShippingAddress($shippingAddress);
        }

        $billingAddress = $this->denormalizeObject(
            $data,
            'billing_address',
            static::ADDRESSES_TYPE,
            $format,
            $context
        );
        if (!empty($billingAddress)) {
            $object->setBillingAddress($billingAddress);
        }

        $object->setCreatedAt(
            $this->denormalizeObject(
                $data,
                'created_at',
                'DateTime',
                $format,
                array_merge($context, ['type' => 'datetime', 'format' => 'Y-m-d H:i:s'])
            )
        );

        $object->setUpdatedAt(
            $this->denormalizeObject(
                $data,
                'updated_at',
                'DateTime',
                $format,
                array_merge($context, ['type' => 'datetime', 'format' => 'Y-m-d H:i:s'])
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Account;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return (is_array($data) || is_string($data)) && $type == static::ACCOUNT_TYPE;
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
}
