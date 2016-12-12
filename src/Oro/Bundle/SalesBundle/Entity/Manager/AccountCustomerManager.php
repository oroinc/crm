<?php

namespace Oro\Bundle\SalesBundle\Entity\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityNotFoundException;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;
use Oro\Bundle\SalesBundle\Exception\Customer\InvalidCustomerRelationEntityException;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;

class AccountCustomerManager
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var ConfigProvider */
    protected $provider;

    /** @var EntityNameResolver */
    protected $nameResolver;

    /**
     * @param DoctrineHelper     $doctrineHelper
     * @param ConfigProvider     $provider
     * @param EntityNameResolver $nameResolver
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ConfigProvider $provider,
        EntityNameResolver $nameResolver
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->provider       = $provider;
        $this->nameResolver   = $nameResolver;
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
     * Creates new Customer from provided Account
     *
     * @param Account $account
     *
     * @return Customer
     */
    public static function createCustomerFromAccount(Account $account)
    {
        $customer = new Customer();

        return $customer->setTarget($account);
    }

    /**
     * @param object $target
     *
     * @return Customer
     *
     * @throws EntityNotFoundException
     */
    public function getAccountCustomerByTarget($target)
    {
        $customerRepo = $this->getCustomerRepository();
        if ($target instanceof Account) {
            $customerFields = $this->getCustomerTargetFields();
            $customer       = $customerRepo->getAccountCustomer($target, $customerFields);
            if (!$customer) {
                $customer = self::createCustomerFromAccount($target);
            }
        } else {
            $targetClassName = ClassUtils::getClass($target);
            $this->assertValidTarget($targetClassName);
            $targetField = self::getCustomerTargetField($targetClassName);
            $id          = $this->doctrineHelper->getSingleEntityIdentifier($target);
            $customer    = $customerRepo->findOneBy([$targetField => $id]);

            if (!$customer) {
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
    protected function getCustomerTargetFields()
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
        if (!in_array($targetClassName, $this->provider->getCustomerClasses())) {
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
