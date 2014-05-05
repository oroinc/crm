<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\OrderRepository;

class OrderListener
{
    /**
     * @var array
     */
    protected $ordersForUpdate = array();

    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        /** @var Order $entity */
        $entity = $event->getEntity();
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
        if ($this->isOrderValid($entity) && array_key_exists('subtotalAmount', $event->getEntityChangeSet())) {
            $this->ordersForUpdate[$entity->getId()] = true;
        }
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(LifecycleEventArgs $event)
    {
        /** @var Order $entity */
        $entity = $event->getEntity();
        if ($this->isOrderValid($entity) && !empty($this->ordersForUpdate[$entity->getId()])) {
            $this->recalculateCustomerLifetime($event->getEntityManager(), $entity->getCustomer());
            unset($this->ordersForUpdate[$entity->getId()]);
        }
    }

    /**
     * @param Order|object $order
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
     * @param Customer $customer
     */
    protected function recalculateCustomerLifetime(EntityManager $entityManager, Customer $customer)
    {
        $oldLifetime = $customer->getLifetime();

        /** @var OrderRepository $orderRepository */
        $orderRepository = $entityManager->getRepository('OroCRMMagentoBundle:Order');
        $newLifetime = $orderRepository->getCustomerOrdersSubtotalAmount($customer);

        if ($newLifetime != $oldLifetime) {
            $entityManager->getUnitOfWork()->scheduleExtraUpdate(
                $customer,
                array('lifetime' => array($oldLifetime, $newLifetime))
            );
        }
    }
}
