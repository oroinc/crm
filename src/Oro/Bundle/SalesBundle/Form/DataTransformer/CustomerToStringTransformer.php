<?php

namespace Oro\Bundle\SalesBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\AccountBundle\Entity\Account;

use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;

class CustomerToStringTransformer implements DataTransformerInterface
{
    /** @var DataTransformerInterface */
    protected $entityToStringTransformer;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var ConfigProvider */
    protected $provider;

    /**
     * @param DataTransformerInterface $entityToStringTransformer
     * @param DoctrineHelper           $doctrineHelper
     * @param ConfigProvider           $provider
     */
    public function __construct(
        DataTransformerInterface $entityToStringTransformer,
        DoctrineHelper $doctrineHelper,
        ConfigProvider $provider
    ) {
        $this->entityToStringTransformer = $entityToStringTransformer;
        $this->doctrineHelper            = $doctrineHelper;
        $this->provider                  = $provider;
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
            $account  = (new Account())
                ->setName($data['value']);
            $customer = $this->createCustomer()
                ->setTarget($account);

            $this->doctrineHelper->getEntityManager($account)->persist($account);

            return $customer;
        }

        $target       = $this->entityToStringTransformer->reverseTransform($value);
        $customerRepo = $this->doctrineHelper->getEntityRepository(Customer::class);
        if ($target instanceof Account) {
            $targetField = 'account';
            $criteria    = [
                $targetField => $this->doctrineHelper->getEntityIdentifier($target),
            ];
            foreach ($this->provider->getCustomerClasses() as $customerClass) {
                $customerField            = ExtendHelper::buildAssociationName(
                    $customerClass,
                    CustomerScope::ASSOCIATION_KIND
                );
                $criteria[$customerField] = null;
            }
            $customer = $customerRepo->findOneBy($criteria);
        } else {
            $targetField = ExtendHelper::buildAssociationName(
                ClassUtils::getClass($target),
                CustomerScope::ASSOCIATION_KIND
            );
            $customer    = $customerRepo
                ->findOneBy([
                    $targetField => $this->doctrineHelper->getEntityIdentifier($target),
                ]);
        }

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
