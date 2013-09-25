<?php

namespace OroCRM\Bundle\AccountBundle\ImportExport\Serializer\Normalizer;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\AbstractContextModeAwareNormalizer;

use OroCRM\Bundle\AccountBundle\Entity\Account;

class AccountNormalizer extends AbstractContextModeAwareNormalizer
{
    const FULL_MODE  = 'full';
    const SHORT_MODE = 'short';
    const ACCOUNT_TYPE  = 'OroCRM\Bundle\AccountBundle\Entity\Account';

    public function __construct()
    {
        parent::__construct(array(self::FULL_MODE, self::SHORT_MODE));
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
}
