<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\MagentoBundle\Entity\Customer as MagentoCustomer;
use Oro\Bundle\SalesBundle\Entity\Customer as SalesCustomer;

/**
 * This listener synchronizes account of MagentoCustomer and SalesCustomer
 *
 * It should be moved to crm-magento-bridge once it's created and thought about
 * possibility to remove this listener and use just one field without duplicating account information.
 */
class CustomerAccountChangeListener
{
    /** @var SalesCustomer[] */
    protected $changedCustomers = [];

    /**
     * Stores MagentoCustomers with changed Account
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
     * Syncs Accounts of MagentoCustomers and SalesCustomers
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        if (!$this->changedCustomers) {
            return;
        }
        $needFlush = false;

        /** @var MagentoCustomer $magentoCustomer */
        foreach ($this->changedCustomers as $customer) {
            $magentoCustomer = $customer->getTarget();
            $account         = $customer->getAccount();
            $magentoAccount  = $magentoCustomer->getAccount();
            if ($account !== $magentoAccount) {
                $magentoCustomer->setAccount($account);
                $needFlush = true;
            }
        }
        $this->changedCustomers = [];
        $em = $args->getEntityManager();
        if ($needFlush) {
            $em->flush();
        }
    }

    /**
     * Prepare Sales Customers which account has been changed and target is Magento Customer
     *
     * @param UnitOfWork $uow
     * @param object[]   $entities
     */
    protected function prepareChangedCustomers(UnitOfWork $uow, array $entities)
    {
        foreach ($entities as $oid => $entity) {
            if (!($entity instanceof SalesCustomer)) {
                continue;
            }
            if (!($entity->getTarget() instanceof MagentoCustomer)) {
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
