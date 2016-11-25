<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\Entity\AccountAwareInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\EntityExtendBundle\Exception\InvalidRelationEntityException;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;

class AccountCustomerHelper
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var ConfigProvider */
    protected $provider;

    /** @var EntityNameResolver */
    protected $nameResolver;

    /**
     * @param DoctrineHelper           $doctrineHelper
     * @param ConfigProvider           $provider
     * @param EntityNameResolver       $nameResolver
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ConfigProvider $provider,
        EntityNameResolver $nameResolver
    ) {
        $this->doctrineHelper            = $doctrineHelper;
        $this->provider                  = $provider;
        $this->nameResolver              = $nameResolver;
    }

    /**
     * @param Customer $customer
     *
     * @return Account
     */
    public static function getTargetCustomerOrAccount(Customer $customer)
    {
        return $customer->getCustomerTarget() ?: $customer->getAccount();
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
     * Creates new Customer with provided Account or with new Account
     * with provided accountName
     *
     * @param Account|null $account
     *
     * @param string|null  $accountName
     *
     * @return Customer
     */
    public static function createCustomerFromAccount(Account $account = null, $accountName = null)
    {
        $customer = new Customer();

        return $account
            ? $customer->setAccount($account)
            : $customer->setAccount((new Account())->setName($accountName));
    }

    /**
     * @param $target
     *
     * @return Customer
     */
    public function createCustomerFromTarget($target)
    {
        $this->assertValidTarget($target);

        return $this->doCreateCustomerFromTarget($target);
    }

    /**
     * @param object $target
     *
     * @return Customer|null
     */
    public function getOrCreateAccountCustomerByTarget($target)
    {
        $customerRepo = $this->getCustomerRepository();
        if ($target instanceof Account) {
            $customerFields = $this->getCustomerTargetFields();
            $customer       = $customerRepo->getAccountCustomer($target, $customerFields);
            if (!$customer) {
                $customer = self::createCustomerFromAccount($target);
            }
        } else {
            $targetEntityClassName = ClassUtils::getClass($target);
            $this->assertValidTarget($targetEntityClassName);
            $targetField           = self::getCustomerTargetField($targetEntityClassName);
            $customer              = $customerRepo->getCustomerByTargetCustomer(
                $this->doctrineHelper->getSingleEntityIdentifier($target),
                $targetField
            );
            if (!$customer) {
                return $this->doCreateCustomerFromTarget($target);
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
     * @param string $targetEntityClassName
     */
    protected function assertValidTarget($targetEntityClassName)
    {
        if (!in_array($targetEntityClassName, $this->provider->getCustomerClasses())) {
            throw new InvalidRelationEntityException(
                sprintf('object of class "%s" is not valid customer target', $targetEntityClassName)
            );
        }
    }

    /**
     * @param object $target
     *
     * @return Customer
     */
    protected function doCreateCustomerFromTarget($target)
    {
        $account = null;
        if ($target instanceof AccountAwareInterface) {
            $account = $target->getAccount();
        }
        $customer = $account
            ? self::createCustomerFromAccount($account)
            : self::createCustomerFromAccount(null, $this->nameResolver->getName($target));

        return $customer->setCustomerTarget($target);
    }

    /**
     * @return CustomerRepository
     */
    protected function getCustomerRepository()
    {
        return $this->doctrineHelper->getEntityRepository(Customer::class);
    }
}
