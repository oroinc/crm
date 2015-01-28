<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;

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
    public function prePersist(LifecycleEventArgs $event)
    {
        /** @var Order $entity */
        $entity = $event->getEntity();

        // if new order has valuable subtotal
        if ($this->isOrderValid($entity) && $entity->getSubtotalAmount()) {
            $this->recalculateCustomerLifetime($event->getEntityManager(), $entity);
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
        if ($this->isInProgress || count($this->ordersForUpdate) === 0) {
            return;
        }

        $needFlush = false;
        $orders = $this->getChangedOrders($event->getEntityManager()->getUnitOfWork());
        foreach ($orders as $order) {
            // if order was scheduled for update
            if (!empty($this->ordersForUpdate[$order->getId()])) {
                $needFlush |= $this->recalculateCustomerLifetime($event->getEntityManager(), $order);
                unset($this->ordersForUpdate[$order->getId()]);
            }
        }

        if ($needFlush) {
            $this->isInProgress = true;
            $event->getEntityManager()->flush();
            $this->isInProgress = false;
        }
    }

    /**
     * @param UnitOfWork $uow
     * @return array|Order[]
     */
    protected function getChangedOrders(UnitOfWork $uow)
    {
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

        return array_filter(
            $entities,
            function ($entity) {
                return $this->isOrderValid($entity);
            }
        );
    }

    /**
     * @param Order|object $order
     *
     * @return bool
     */
    protected function isOrderValid($order)
    {
        return $order instanceof Order
            && $order->getCustomer() instanceof Customer;
    }

    /**
     * @param EntityManager $entityManager
     * @param Order $order
     *
     * @return bool         Returns 'true' when real changes were provided
     */
    protected function recalculateCustomerLifetime(EntityManager $entityManager, Order $order)
    {
        $customer = $order->getCustomer();
        $oldLifetime = (float)$customer->getLifetime();

        /** @var OrderRepository $orderRepository */
        $orderRepository = $entityManager->getRepository('OroCRMMagentoBundle:Order');
        $newLifetime = $orderRepository->getCustomerOrdersSubtotalAmount($customer);

        if ($newLifetime !== $oldLifetime) {
            $customer->setLifetime($newLifetime);

            return true;
        }

        return false;
    }
}
