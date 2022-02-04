<?php

namespace Oro\Bundle\SalesBundle\EventListener\Customers;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerTrait;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;

/**
 * Creates missing associations related to the existing customers.
 */
class CustomerAssociationListener
{
    use OptionalListenerTrait;

    /** @var array [object[]] */
    protected $createdTargetCustomers = [];

    /** @var ConfigProvider */
    protected $customerConfigProvider;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var AccountCustomerManager */
    protected $manager;

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
     * Collect created customer targets
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        if (!$this->enabled) {
            return;
        }

        $uow = $args->getEntityManager()->getUnitOfWork();
        $this->prepareCreatedCustomers($uow->getScheduledEntityInsertions());
    }

    /**
     * Check for required existence of related customer associations for collected created targets
     * and creates missing
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (!$this->enabled) {
            return;
        }

        if (!$this->createdTargetCustomers) {
            return;
        }
        $qb             = $this->getCustomerRepository()->createQueryBuilder('ca');
        $invalidTargets = [];
        $em             = $this->getEntityManager();
        // find all target customers which do not have related customer association
        foreach ($this->createdTargetCustomers as $class => $classTargets) {
            $customersIds = array_reduce(
                $classTargets,
                function ($ids, $target) {
                    $id       = $this->doctrineHelper->getSingleEntityIdentifier($target);
                    $ids[$id] = $target;

                    return $ids;
                },
                []
            );
            $targetField  = AccountCustomerManager::getCustomerTargetField($class);
            $qb
                ->select(sprintf('IDENTITY(ca.%s) AS id', $targetField))
                ->where(sprintf('IDENTITY(ca.%s) IN (:ids)', $targetField))
                ->setParameter('ids', array_keys($customersIds));
            $existingResult         = $qb->getQuery()->getArrayResult();
            $existing               = array_map(
                function (array $item) {
                    return $item['id'];
                },
                $existingResult
            );
            $invalidTargets[$class] = array_diff_key($customersIds, array_flip($existing));
        }
        // create related customer associations
        $needFlush = false;
        foreach ($invalidTargets as $class => $invalidTargetEntities) {
            foreach ($invalidTargetEntities as $item) {
                $customer = new Customer();
                $customer->setTarget($this->manager->createAccountForTarget($item), $item);
                $em->persist($customer);
                $needFlush = true;
            }
        }
        $this->createdTargetCustomers = [];
        if ($needFlush) {
            $em->flush();
        }
    }

    /**
     * Prepare created target customers which have sales customer association
     *
     * @param object[] $entities
     */
    protected function prepareCreatedCustomers(array $entities)
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
