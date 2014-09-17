<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\PersistentCollection;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\OrderRepository;

class OrderListener
{
    /** @var array */
    protected $ordersForUpdate = [];

    /** @var bool */
    protected $isInProgress = false;

    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        /** @var Order $entity */
        $entity = $event->getEntity();

        // if new order has valuable subtotal
        if ($this->isOrderValid($entity) && $entity->getSubtotalAmount() != 0) {
            $this->recalculateCustomerLifetime($event->getEntityManager(), $entity->getCustomer());
        }
    }

    /**
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(PreUpdateEventArgs $event)
    {
        $entity = $event->getEntity();

        // if subtotal or status has been changed
        if ($this->isOrderValid($entity)
            && array_intersect(['subtotalAmount', 'status'], array_keys($event->getEntityChangeSet()))
        ) {
            $this->ordersForUpdate[$entity->getId()] = true;
        }
    }

    /**
     * @param PostFlushEventArgs $event
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        if ($this->isInProgress || empty($this->ordersForUpdate)) {
            return;
        }

        $uow      = $event->getEntityManager()->getUnitOfWork();
        $entities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityDeletions(),
            $uow->getScheduledEntityUpdates()
        );

        $collections = array_merge(
            $uow->getScheduledCollectionDeletions(),
            $uow->getScheduledCollectionUpdates()
        );

        /** @var PersistentCollection $collectionToChange */
        foreach ($collections as $collectionToChange) {
            $entities = array_merge($entities, $collectionToChange->unwrap()->toArray());
        }

        $needFlush = false;
        /** @var Order $entity */
        foreach ($entities as $entity) {
            // if order was scheduled for update
            if ($this->isOrderValid($entity) && !empty($this->ordersForUpdate[$entity->getId()])) {
                $needFlush |= $this->recalculateCustomerLifetime($event->getEntityManager(), $entity->getCustomer());
                unset($this->ordersForUpdate[$entity->getId()]);
            }
        }

        if ($needFlush) {
            $this->isInProgress = true;
            $event->getEntityManager()->flush();
            $this->isInProgress = false;
        }
    }

    /**
     * @param Order|object $order `
     *
     * @return bool
     */
    protected function isOrderValid($order)
    {
        if (!$order instanceof Order) {
            return false;
        }

        $customer = $order->getCustomer();
        if (!$customer || !$customer instanceof Customer) {
            return false;
        }

        return true;
    }

    /**
     * @param EntityManager $entityManager
     * @param Customer      $customer
     *
     * @return bool         Returns 'true' when real changes were provided
     */
    protected function recalculateCustomerLifetime(EntityManager $entityManager, Customer $customer)
    {
        $oldLifetime = $customer->getLifetime();

        /** @var OrderRepository $orderRepository */
        $orderRepository = $entityManager->getRepository('OroCRMMagentoBundle:Order');
        $newLifetime     = $orderRepository->getCustomerOrdersSubtotalAmount($customer);

        if ($newLifetime != $oldLifetime) {
            $customer->setLifetime($newLifetime);

            return true;
        }

        return false;
    }
}
