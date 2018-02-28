<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\ChannelBundle\EventListener\ChannelDoctrineListener;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\Repository\CustomerRepository;

class OrderListener
{
    /** @var ChannelDoctrineListener */
    protected $channelDoctrineListener;

    /** @var array */
    protected $ordersForUpdate = [];

    /**
     * @param ChannelDoctrineListener $channelDoctrineListener
     */
    public function __construct(ChannelDoctrineListener $channelDoctrineListener)
    {
        $this->channelDoctrineListener = $channelDoctrineListener;
    }

    /**
     * @param Order              $entity
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Order $entity, LifecycleEventArgs $event)
    {
        // if new order has valuable subtotal and status
        if ($this->isOrderSupported($entity)
            && $entity->getSubtotalAmount()
            && !$entity->isCanceled()
        ) {
            $this->channelDoctrineListener->initializeFromEventArgs($event);
            $this->updateCustomerLifetime($event->getEntityManager(), $entity);
        }
    }

    /**
     * @param Order              $entity
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Order $entity, PreUpdateEventArgs $event)
    {
        // if subtotal or status has been changed
        if ($this->isOrderSupported($entity)
            && array_intersect(['subtotalAmount', 'discountAmount', 'status'], array_keys($event->getEntityChangeSet()))
        ) {
            $this->ordersForUpdate[$entity->getId()] = true;
        }
    }

    /**
     * @param PostFlushEventArgs $event
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        if (count($this->ordersForUpdate) === 0) {
            return;
        }

        $orders = $this->getChangedOrders($event->getEntityManager()->getUnitOfWork());
        foreach ($orders as $order) {
            // if order was scheduled for update
            if (!empty($this->ordersForUpdate[$order->getId()])) {
                $this->channelDoctrineListener->initializeFromEventArgs($event);
                $this->updateCustomerLifetime($event->getEntityManager(), $order);
                unset($this->ordersForUpdate[$order->getId()]);
            }
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
                return $entity instanceof Order && $this->isOrderSupported($entity);
            }
        );
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    protected function isOrderSupported(Order $order)
    {
        return $order->getCustomer() instanceof Customer;
    }

    /**
     * @param EntityManager $entityManager
     * @param Order         $order
     */
    protected function updateCustomerLifetime(EntityManager $entityManager, Order $order)
    {
        /** @var CustomerRepository $customerRepository */
        $customerRepository = $entityManager->getRepository('OroMagentoBundle:Customer');

        $subtotalAmount = $order->getSubtotalAmount();
        if ($subtotalAmount) {
            $discountAmount = $order->getDiscountAmount();
            $lifetimeValue  = $discountAmount
                ? $subtotalAmount - abs($discountAmount)
                : $subtotalAmount;
            // if order status changed to canceled we should remove order lifetime value from customer lifetime
            if ($order->isCanceled()) {
                $lifetimeValue *= -1;
            }

            $customer = $order->getCustomer();
            $customerRepository->updateCustomerLifetimeValue($customer, $lifetimeValue);

            // schedule lifetime history update
            if ($customer->getAccount()) {
                $this->channelDoctrineListener->scheduleEntityUpdate(
                    $customer,
                    $customer->getAccount(),
                    $customer->getDataChannel()
                );
            }
        }
    }
}
