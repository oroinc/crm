<?php

namespace Oro\Bundle\SalesBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;

use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Customer as SalesCustomer;

/**
 * This listener synchronizes account of B2bCustomer and SalesCustomer
 */
class CustomerAccountChangeSubscriber implements EventSubscriber
{
    /** @var SalesCustomer[] */
    protected $changedCustomers = [];

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush',
            'postFlush',
        ];
    }

    /**
     * Stores B2bCustomers with changed Account
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();

        $this->prepareChangedCustomers($uow, $uow->getScheduledEntityInsertions());
        $this->prepareChangedCustomers($uow, $uow->getScheduledEntityUpdates());
    }

    /**
     * Syncs Accounts of B2bCustomers and SalesCustomers
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $needFlush = false;

        /** @var B2bCustomer $customer */
        foreach ($this->changedCustomers as $customerAssociation) {
            $customer = $customerAssociation->getTarget();
            $account = $customerAssociation->getAccount();
            $customerAccount = $customer->getAccount();
            if ($account !== $customerAccount) {
                $customer->setAccount($account);
                $needFlush = true;
            }
        }

        $this->changedCustomers = [];
        if ($needFlush) {
            $args->getEntityManager()->flush();
        }
    }

    /**
     * Prepare Sales Customers which account has been changed and target is B2bCustomer
     *
     * @param UnitOfWork $uow
     * @param object[]   $entities
     */
    protected function prepareChangedCustomers(UnitOfWork $uow, array $entities)
    {
        foreach ($entities as $oid => $entity) {
            if (!$entity instanceof SalesCustomer) {
                continue;
            }

            if (!$entity->getTarget() instanceof B2bCustomer) {
                continue;
            }

            $changeSet = $uow->getEntityChangeSet($entity);
            if (!isset($changeSet['account'])) {
                continue;
            }

            $this->changedCustomers[$oid] = $entity;
        }
    }
}
