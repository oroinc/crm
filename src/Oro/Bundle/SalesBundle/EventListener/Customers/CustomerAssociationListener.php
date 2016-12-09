<?php

namespace Oro\Bundle\SalesBundle\EventListener\Customers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Bundle\SalesBundle\Entity\Customer;

class CustomerAssociationListener
{
    /** @var [object[]] */
    protected $createdTargetCustomers = [];

    /** @var ConfigProvider */
    protected $customerConfigProvider;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var AccountCustomerManager */
    protected $manager;

    /**
     * @param AccountCustomerManager $manager
     * @param ConfigProvider         $customerConfigProvider
     * @param DoctrineHelper         $helper
     */
    public function __construct(
        AccountCustomerManager $manager,
        ConfigProvider $customerConfigProvider,
        DoctrineHelper $helper
    ) {
        $this->manager                = $manager;
        $this->customerConfigProvider = $customerConfigProvider;
        $this->doctrineHelper         = $helper;
    }
    /**
     * Stores MagentoCustomers with changed Account
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();
        $this->prepareCreatedCustomers(
            $uow,
            $uow->getScheduledEntityInsertions()
        );
    }

    /**
     * Syncs Accounts of MagentoCustomers and SalesCustomers
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (!$this->createdTargetCustomers) {
            return;
        }
        $qb = $this->getCustomerRepository()->createQueryBuilder('ca');
        $invalidTargets = [];
        $em = $this->getEntityManager();
        foreach ($this->createdTargetCustomers as $class => $classTargets) {
            $customersIds = array_reduce(
                $classTargets,
                function($ids, $target) {
                    $id       = $this->doctrineHelper->getSingleEntityIdentifier($target);
                    $ids[$id] = $target;

                    return $ids;
                },
                []
            );
            $targetField = AccountCustomerManager::getCustomerTargetField($class);
            $qb
                ->select(sprintf('IDENTITY(ca.%s) AS id', $targetField))
                ->where(sprintf('IDENTITY(ca.%s) IN (:ids)', $targetField))
                ->setParameter('ids', array_keys($customersIds));
            $result                 = $qb->getQuery()->getArrayResult();
            $mapped = array_map(function(array $item) {return $item['id'];}, $result);
            $invalidTargets[$class] = array_diff_key($customersIds, $mapped);
        }

        foreach ($invalidTargets as $class => $invalidTargetEntities) {
            foreach ($invalidTargetEntities as $item) {
                $account = $this->manager->createAccountForTarget($item);
                $customer = AccountCustomerManager::createCustomer($account, $item);
                $em->persist($customer);
            }
        }
    }

    /**
     * Prepare created target customers which have sales customer association
     *
     * @param UnitOfWork $uow
     * @param object[]   $entities
     */
    protected function prepareCreatedCustomers(UnitOfWork $uow, array $entities)
    {
        foreach ($entities as $oid => $entity) {
            $class = ClassUtils::getClass($entity);
            if ($this->customerConfigProvider->isCustomerClass($class)) {
                if (!isset($this->createdTargetCustomers[$class])) {
                    $this->createdTargetCustomers[$class] = [$entity];
                } else {
                    $this->createdTargetCustomers[$class][] = $entity;
                }
            }
        }
    }

    /**
     * @return CustomerRepository
     */
    protected function getCustomerRepository()
    {
        return $this->doctrineHelper->getEntityRepository(Customer::class);
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->doctrineHelper->getEntityManager(Customer::class);
    }
}
