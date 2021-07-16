<?php

namespace Oro\Bundle\SalesBundle\Entity\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityNotFoundException;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;
use Oro\Bundle\SalesBundle\Exception\Customer\InvalidCustomerRelationEntityException;
use Oro\Bundle\SalesBundle\Provider\Customer\AccountCreation\AccountProviderInterface;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;

class AccountCustomerManager
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var ConfigProvider */
    protected $provider;

    /** @var AccountProviderInterface */
    protected $accountProvider;

    public function __construct(
        DoctrineHelper $doctrineHelper,
        ConfigProvider $provider,
        AccountProviderInterface $accountProvider
    ) {
        $this->doctrineHelper  = $doctrineHelper;
        $this->provider        = $provider;
        $this->accountProvider = $accountProvider;
    }

    /**
     * @param $targetClassName
     *
     * @return string
     */
    public static function getCustomerTargetField($targetClassName)
    {
        return ExtendHelper::buildAssociationName(
            $targetClassName,
            CustomerScope::ASSOCIATION_KIND
        );
    }

    /**
     * Creates new Customer from provided Account and optionally target
     *
     * @param Account     $account
     *
     * @param object|null $target
     *
     * @return Customer
     */
    public static function createCustomer(Account $account, $target = null)
    {
        $customer = new Customer();

        return $customer->setTarget($account, $target);
    }

    /**
     * @param object $target
     *
     * @return Account
     */
    public function createAccountForTarget($target)
    {
        $targetClassName = ClassUtils::getClass($target);
        $this->assertValidTarget($targetClassName);

        return $this->accountProvider->getAccount($target);
    }

    /**
     * @param object $target
     * @param bool   $throwExceptionOnNotFound
     *
     * @return Customer
     * @throws EntityNotFoundException
     */
    public function getAccountCustomerByTarget($target, $throwExceptionOnNotFound = true)
    {
        $customerRepo = $this->getCustomerRepository();
        if ($target instanceof Account) {
            $customerFields = $this->getCustomerTargetFields();
            if ($this->doctrineHelper->isNewEntity($target)) {
                return self::createCustomer($target);
            }
            $customer       = $customerRepo->getAccountCustomer($target, $customerFields);
            if (!$customer) {
                $customer = static::createCustomer($target);
            }
        } else {
            $targetClassName = ClassUtils::getClass($target);
            $this->assertValidTarget($targetClassName);
            $targetField = self::getCustomerTargetField($targetClassName);
            $id          = $this->doctrineHelper->getSingleEntityIdentifier($target, false);
            $customer    = $id
                ? $customerRepo->findOneBy([$targetField => $id])
                : null;

            if (!$customer && $throwExceptionOnNotFound) {
                throw new EntityNotFoundException(
                    sprintf(
                        'Sales Customer for target of type "%s" and identifier %s was not found',
                        $targetClassName,
                        $id
                    )
                );
            }
        }

        return $customer;
    }

    /**
     * @return array
     */
    public function getCustomerTargetFields()
    {
        $customerFields = [];
        foreach ($this->provider->getCustomerClasses() as $customerClass) {
            $customerField    = ExtendHelper::buildAssociationName(
                $customerClass,
                CustomerScope::ASSOCIATION_KIND
            );
            $customerFields[] = $customerField;
        }

        return $customerFields;
    }

    /**
     * @param string $targetClassName
     */
    protected function assertValidTarget($targetClassName)
    {
        if (!in_array($targetClassName, $this->provider->getCustomerClasses(), true)) {
            throw new InvalidCustomerRelationEntityException(
                sprintf('object of class "%s" is not valid customer target', $targetClassName)
            );
        }
    }

    /**
     * @return CustomerRepository
     */
    protected function getCustomerRepository()
    {
        return $this->doctrineHelper->getEntityRepository(Customer::class);
    }
}
