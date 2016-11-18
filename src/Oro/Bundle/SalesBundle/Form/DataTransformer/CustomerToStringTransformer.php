<?php

namespace Oro\Bundle\SalesBundle\Form\DataTransformer;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;
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
            $customer = $this->createCustomer()
                ->setTarget($account);

            $this->doctrineHelper->getEntityManager($account)->persist($account);

            return $customer;
        }

        $target = $this->entityToStringTransformer->reverseTransform($value);
        $targetField = $target instanceof Account
                ? 'account'
                : ExtendHelper::buildAssociationName(
                    ClassUtils::getClass($target),
                    CustomerScope::ASSOCIATION_KIND
                );
        $customer = $this->doctrineHelper->getEntityRepository(Customer::class)
            ->findOneBy([
                $targetField => $this->doctrineHelper->getEntityIdentifier($target),
            ]);
        if (!$customer) {
            $customer = $this->createCustomer()
                ->setTarget($target);
        }

        return $customer;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value instanceof Customer) {
            $target = $value->getTarget();
            if ($target instanceof Account && !$target->getId()) {
                return json_encode([
                    'value' => $target->getName(),
                ]);
            } else {
                $value = $target;
            }
        }

        return $this->entityToStringTransformer->transform($value);
    }

    /**
     * @return Customer
     */
    protected function createCustomer()
    {
        return new Customer();
    }
}
