<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\EventListener\OrderListener;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class OrderListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param Order|object $order
     * @param float|null $newLifetime
     * @dataProvider persistDataProvider
     */
    public function testPersist($order, $newLifetime = null)
    {
        if ($newLifetime) {
            $entityManager = $this->createEntityManagerMock($order->getCustomer(), $newLifetime);
        } else {
            $entityManager = $this->createEntityManagerMock();
        }

        $listener = new OrderListener();
        $listener->postPersist(new LifecycleEventArgs($order, $entityManager));
    }

    /**
     * @return array
     */
    public function persistDataProvider()
    {
        return array(
            'not an order'       => array(new \DateTime()),
            'zero subtotal'      => array($this->createOrder(null, 0)),
            'no customer'        => array($this->createOrder(null)),
            'incorrect customer' => array($this->createOrder(new \DateTime())),
            'equal lifetime'     => array($this->createOrder($this->createCustomer(20)), 20),
            'updated lifetime'   => array($this->createOrder($this->createCustomer(20)), 30),
        );
    }

    /**
     * @param Order|object $order
     * @param float|null $newLifetime
     * @param array $changeSet
     * @dataProvider updateDataProvider
     */
    public function testUpdate($order, $newLifetime = null, array $changeSet = array())
    {
        $isUpdateRequired = array_intersect(array('subtotalAmount', 'status'), array_keys($changeSet));

        if ($isUpdateRequired && $newLifetime) {
            $entityManager = $this->createEntityManagerMock($order->getCustomer(), $newLifetime);
        } else {
            $entityManager = $this->createEntityManagerMock();
        }

        $listener = new OrderListener();
        $listener->preUpdate(new PreUpdateEventArgs($order, $entityManager, $changeSet));

        if ($isUpdateRequired) {
            $this->assertAttributeEquals(array($order->getId() => true), 'ordersForUpdate', $listener);
        } else {
            $this->assertAttributeEmpty('ordersForUpdate', $listener);
        }

        $listener->postUpdate(new LifecycleEventArgs($order, $entityManager));

        $this->assertAttributeEmpty('ordersForUpdate', $listener);
    }

    /**
     * @return array
     */
    public function updateDataProvider()
    {
        return array(
            'not an order'         => array(new \DateTime()),
            'no customer'          => array($this->createOrder(null)),
            'incorrect customer'   => array($this->createOrder(new \DateTime())),
            'subtotal not changed' => array($this->createOrder($this->createCustomer())),
            'equal lifetime'       => array(
                $this->createOrder($this->createCustomer(20)),
                20,
                array('status' => array('pending', 'canceled')),
            ),
            'updated lifetime'     => array(
                $this->createOrder($this->createCustomer(20)),
                30,
                array('subtotalAmount' => array(0, 10)),
            ),
        );
    }

    /**
     * @param Customer|null $customer
     * @param float|null $newLifetime
     * @return EntityManager
     * @throws \PHPUnit_Framework_Exception
     */
    protected function createEntityManagerMock($customer = null, $newLifetime = null)
    {
        $orderRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('getCustomerOrdersSubtotalAmount'))
            ->getMock();

        if ($customer && $newLifetime) {
            $orderRepository->expects($this->once())->method('getCustomerOrdersSubtotalAmount')
                ->with($customer)->will($this->returnValue($newLifetime));
        } else {
            $orderRepository->expects($this->never())->method('getCustomerOrdersSubtotalAmount');
        }

        $unitOfWork = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->setMethods(array('scheduleExtraUpdate'))
            ->getMock();

        if ($customer && $newLifetime && $customer->getLifetime() != $newLifetime) {
            $unitOfWork->expects($this->once())->method('scheduleExtraUpdate')->with(
                $customer,
                array('lifetime' => array($customer->getLifetime(), $newLifetime))
            );
        } else {
            $unitOfWork->expects($this->never())->method('scheduleExtraUpdate');
        }

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepository', 'getUnitOfWork'))
            ->getMock();
        $entityManager->expects($this->any())->method('getRepository')->with('OroCRMMagentoBundle:Order')
            ->will($this->returnValue($orderRepository));
        $entityManager->expects($this->any())->method('getUnitOfWork')
            ->will($this->returnValue($unitOfWork));

        return $entityManager;
    }

    /**
     * @param Customer|object|null $customer
     * @param float $subtotal
     * @return Order
     */
    protected function createOrder($customer = null, $subtotal = 10.1)
    {
        $order = new Order();
        $order->setId(1);
        if ($customer) {
            $order->setCustomer($customer);
        }
        $order->setSubtotalAmount($subtotal);

        return $order;
    }

    /**
     * @param float|null $lifetime
     * @return Customer
     */
    protected function createCustomer($lifetime = null)
    {
        $customer = new Customer();
        $customer->setId(2);
        if ($lifetime) {
            $customer->setLifetime($lifetime);
        }

        return $customer;
    }
}
