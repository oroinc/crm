<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
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
            'not an order'       => [new \DateTime()],
            'zero subtotal'      => [$this->createOrder(null, 0)],
            'no customer'        => [$this->createOrder(null)],
            'incorrect customer' => [$this->createOrder(new \DateTime())],
            'equal lifetime'     => [$this->createOrder($this->createCustomer(20)), 20],
            'updated lifetime'   => [$this->createOrder($this->createCustomer(20)), 30],
        );
    }

    /**
     * @param Order|object $order
     * @param float|null $newLifetime
     * @param array $changeSet
     *
     * @dataProvider updateDataProvider
     */
    public function testUpdate($order, $newLifetime = null, array $changeSet = [])
    {
        $isUpdateRequired = array_intersect(['subtotalAmount', 'status'], array_keys($changeSet));

        if ($isUpdateRequired && $newLifetime) {
            $entityManager = $this->createEntityManagerMock($order->getCustomer(), $newLifetime);
        } else {
            $entityManager = $this->createEntityManagerMock();
        }

        $listener = new OrderListener();
        $listener->preUpdate(new PreUpdateEventArgs($order, $entityManager, $changeSet));

        if ($isUpdateRequired) {
            $this->assertAttributeEquals([$order->getId() => true], 'ordersForUpdate', $listener);
        } else {
            $this->assertAttributeEmpty('ordersForUpdate', $listener);
        }

        $listener->postFlush(new PostFlushEventArgs($entityManager));

        $this->assertObjectHasAttribute('ordersForUpdate', $listener);
    }

    /**
     * @return array
     */
    public function updateDataProvider()
    {
        return array(
            'not an order'         => [new \DateTime()],
            'no customer'          => [$this->createOrder(null)],
            'incorrect customer'   => [$this->createOrder(new \DateTime())],
            'subtotal not changed' => [$this->createOrder($this->createCustomer())],
            'equal lifetime'       => [
                $this->createOrder($this->createCustomer(20)),
                20,
                ['status' => ['pending', 'canceled']],
            ],
            'updated lifetime'     => [
                $this->createOrder($this->createCustomer(20)),
                30,
                ['subtotalAmount' => [0, 10]],
            ],
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
            ->setMethods(['getCustomerOrdersSubtotalAmount'])
            ->getMock();

        if ($customer && $newLifetime) {
            $orderRepository->expects($this->any())->method('getCustomerOrdersSubtotalAmount')
                ->with($customer)->will($this->returnValue($newLifetime));
        } else {
            $orderRepository->expects($this->never())->method('getCustomerOrdersSubtotalAmount');
        }

        $unitOfWork = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->setMethods(['scheduleExtraUpdate'])
            ->getMock();

        if ($customer && $newLifetime && $customer->getLifetime() != $newLifetime) {
            $unitOfWork->expects($this->any())->method('scheduleExtraUpdate')->with(
                $customer,
                ['lifetime' => [$customer->getLifetime(), $newLifetime]]
            );
        } else {
            $unitOfWork->expects($this->never())->method('scheduleExtraUpdate');
        }

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'getUnitOfWork'])
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
     *
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
     *
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
