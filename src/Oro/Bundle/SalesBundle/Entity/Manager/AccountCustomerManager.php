<?php

namespace Oro\Bundle\SalesBundle\Entity\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityNotFoundException;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Factory\CustomerFactory;
use Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;
use Oro\Bundle\SalesBundle\Exception\Customer\InvalidCustomerRelationEntityException;
use Oro\Bundle\SalesBundle\Provider\Customer\AccountCreation\AccountProviderInterface;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;

/**
 * Provides a set of methods to manage associations between Account and other entities classified as customers.
 */
class AccountCustomerManager
{
    public function __construct(
        private DoctrineHelper $doctrineHelper,
        private ConfigProvider $provider,
        private AccountProviderInterface $accountProvider,
        private CustomerFactory $customerFactory,
    ) {
    }

    /**
     * Gets the name of an association to the given target customer type.
     */
    public static function getCustomerTargetField(string $targetClassName): string
    {
        return ExtendHelper::buildAssociationName($targetClassName, CustomerScope::ASSOCIATION_KIND);
    }

    /**
     * Creates an account for the given target customer.
     *
     * @throws InvalidCustomerRelationEntityException when the target is not supported
     */
    public function createAccountForTarget(object $target): Account
    {
        $targetClassName = ClassUtils::getClass($target);
        $this->assertValidTarget($targetClassName);

        return $this->accountProvider->getAccount($target);
    }

    /**
     * Gets an association between an account and a customer.
     *
     * @throws InvalidCustomerRelationEntityException when the target is not supported
     * @throws EntityNotFoundException when a customer association does not exist for an existing target
     */
    public function getAccountCustomerByTarget(object $target, bool $throwExceptionOnNotFound = true): ?Customer
    {
        if ($target instanceof Account) {
            $customer = null;
            if (!$this->doctrineHelper->isNewEntity($target)) {
                $customer = $this->getCustomerRepository()->getAccountCustomer(
                    $target,
                    $this->getCustomerTargetFields()
                );
            }
            if (null === $customer) {
                $customer = $this->customerFactory->createCustomer();
                $customer->setTarget($target, null);
            }
        } else {
            $targetClassName = ClassUtils::getClass($target);
            $this->assertValidTarget($targetClassName);
            $id = $this->doctrineHelper->getSingleEntityIdentifier($target, false);
            $customer = $id
                ? $this->getCustomerRepository()->findOneBy([self::getCustomerTargetField($targetClassName) => $id])
                : null;
            if (null === $customer && $throwExceptionOnNotFound) {
                throw new EntityNotFoundException(sprintf(
                    'Sales Customer for target of type "%s" and identifier %s was not found',
                    $targetClassName,
                    $id
                ));
            }
        }

        return $customer;
    }

    /**
     * @return string[]
     */
    public function getCustomerTargetFields(): array
    {
        $customerFields = [];
        foreach ($this->provider->getCustomerClasses() as $customerClass) {
            $customerFields[] = self::getCustomerTargetField($customerClass);
        }

        return $customerFields;
    }

    private function assertValidTarget(string $targetClassName): void
    {
        if (!\in_array($targetClassName, $this->provider->getCustomerClasses(), true)) {
            throw new InvalidCustomerRelationEntityException(
                sprintf('object of class "%s" is not valid customer target', $targetClassName)
            );
        }
    }

    private function getCustomerRepository(): CustomerRepository
    {
        return $this->doctrineHelper->getEntityRepository(Customer::class);
    }
}
