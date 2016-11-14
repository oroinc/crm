<?php

namespace Oro\Bundle\SalesBundle\Form\DataTransformer;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CustomerToStringTransformer implements DataTransformerInterface
{
    /** @var DataTransformerInterface */
    protected $entityToStringTransformer;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param DataTransformerInterface $entityToStringTransformer
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        DataTransformerInterface $entityToStringTransformer,
        DoctrineHelper $doctrineHelper
    ) {
        $this->entityToStringTransformer = $entityToStringTransformer;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (empty($value)) {
            return;
        }

        if (!is_string($value)) {
            throw new TransformationFailedException('Expected a string.');
        }

        $data = json_decode($value, true);

        if (!is_array($data)) {
            throw new TransformationFailedException('Expected an array after decoding a string.');
        }

        if (!empty($data['value'])) {
            $account = (new Account())
                ->setName($data['value']);
            $this->doctrineHelper->getEntityManager($account)->persist($account);

            return $account;
        }

        return $this->entityToStringTransformer->reverseTransform($value);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value instanceof Account && !$value->getId()) {
            return json_encode([
                'value' => $value->getName(),
            ]);
        }

        return $this->entityToStringTransformer->transform($value);
    }
}
