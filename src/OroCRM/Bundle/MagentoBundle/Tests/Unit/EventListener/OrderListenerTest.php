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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepository;

    /**
     * @param Order|object $order
     * @param float $expectedLifetime
     * @dataProvider prePersistDataProvider
     */
    public function testPrePersist($order, $expectedLifetime = null)
    {
        $entityManager = $this->createEntityManagerMock();
        if ($expectedLifetime) {
            $this->customerRepository->expects($this->once())
                ->method('updateCustomerLifetimeValue')
                ->with($this->isInstanceOf('OroCRM\Bundle\MagentoBundle\Entity\Customer'), $expectedLifetime);
        } else {
            $this->customerRepository->expects($this->never())->method('updateCustomerLifetimeValue');
        }

        $listener = new OrderListener();
        $listener->prePersist(new LifecycleEventArgs($order, $entityManager));
    }

    /**
     * @return array
     */
    public function prePersistDataProvider()
    {
        return [
            'not an order'       => [new \DateTime()],
            'zero subtotal'      => [$this->createOrder(null, 0)],
            'no customer'        => [$this->createOrder(null)],
            'incorrect customer' => [$this->createOrder(new \DateTime())],
            'equal lifetime'     => [$this->createOrder($this->createCustomer(), 10), 10],
            'updated lifetime'   => [$this->createOrder($this->createCustomer(), 15), 15],
            'canceled order'     => [$this->createOrder($this->createCustomer(), 3, Order::STATUS_CANCELED)]
        ];
    }

    /**
     * @param Order|object $order
     * @param array        $changeSet
     *
     * @dataProvider preUpdateDataProvider
     */
    public function testPreUpdate($order, array $changeSet = [])
    {
        $isUpdateRequired = array_intersect(['subtotalAmount', 'status'], array_keys($changeSet));

        if ($isUpdateRequired) {
            $entityManager = $this->createEntityManagerMock($order);
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

        $listener->preUpdate(new PreUpdateEventArgs($order, $entityManager, $changeSet));

        $this->assertObjectHasAttribute('ordersForUpdate', $listener);
    }

    /**
     * @return array
     */
    public function preUpdateDataProvider()
    {
        return [
            'not an order'         => [new \DateTime()],
            'no customer'          => [$this->createOrder(null)],
            'incorrect customer'   => [$this->createOrder(new \DateTime())],
            'subtotal not changed' => [$this->createOrder($this->createCustomer())],
            'equal lifetime'       => [
                $this->createOrder($this->createCustomer(20)),
                ['status' => ['pending', 'canceled']]
            ],
            'updated lifetime'     => [
                $this->createOrder($this->createCustomer(20)),
                ['subtotalAmount' => [0, 10]]
            ]
        ];
    }

    /**
     * @dataProvider postFlushProvider
     * @param Order $order
     * @param null|int $newLifetime
     */
    public function testPostFlush($order, $newLifetime = null)
    {
        if ($newLifetime) {
            $entityManager = $this->createEntityManagerMock($order, $newLifetime);
        } else {
            $entityManager = $this->createEntityManagerMock();
        }

        $listener = new OrderListener();

        if ($newLifetime) {
            $reflectionProperty = new \ReflectionProperty(get_class($listener), 'ordersForUpdate');
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($listener, [1 => true]);
        }

        if ($newLifetime) {
            $this->customerRepository->expects($this->once())
                ->method('updateCustomerLifetimeValue')
                ->with($this->isInstanceOf('OroCRM\Bundle\MagentoBundle\Entity\Customer'), $newLifetime);
        } else {
            $this->customerRepository->expects($this->never())->method('updateCustomerLifetimeValue');
        }

        $listener->postFlush(new PostFlushEventArgs($entityManager));
    }

    /**
     * @return array
     */
    public function postFlushProvider()
    {
        return [
            'not an order'         => [new \DateTime()],
            'no customer'          => [$this->createOrder(null)],
            'incorrect customer'   => [$this->createOrder(new \DateTime())],
            'subtotal not changed' => [$this->createOrder($this->createCustomer())],
            'updated lifetime'     => [$this->createOrder($this->createCustomer(20)), 10.1],
            'decrease lifetime'    => [
                $this->createOrder($this->createCustomer(20), 20, Order::STATUS_CANCELED),
                -20
            ]
        ];
    }

    /**
     * @param Order|null $order
     *
     * @return EntityManager
     * @throws \PHPUnit_Framework_Exception
     */
    protected function createEntityManagerMock($order = null)
    {
        $this->customerRepository = $this
            ->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\Repository\CustomerRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $unitOfWork = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getScheduledEntityInsertions',
                    'getScheduledEntityDeletions',
                    'getScheduledEntityUpdates',
                    'getScheduledCollectionDeletions',
                    'getScheduledCollectionUpdates'
                ]
            )
            ->getMock();

        $unitOfWork
            ->expects($this->any())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue(['OroCRM\Bundle\MagentoBundle\Entity\Order' => $order]));
        $unitOfWork
            ->expects($this->any())
            ->method('getScheduledEntityDeletions')
            ->will($this->returnValue([]));
        $unitOfWork
            ->expects($this->any())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue([]));
        $unitOfWork
            ->expects($this->any())
            ->method('getScheduledCollectionDeletions')
            ->will($this->returnValue([]));
        $unitOfWork
            ->expects($this->any())
            ->method('getScheduledCollectionUpdates')
            ->will($this->returnValue([]));
        $unitOfWork
            ->expects($this->any())
            ->method('commit');

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'getUnitOfWork', 'flush'])
            ->getMock();
        $entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with('OroCRMMagentoBundle:Customer')
            ->will($this->returnValue($this->customerRepository));
        $entityManager
            ->expects($this->any())
            ->method('getUnitOfWork')
            ->will($this->returnValue($unitOfWork));

        return $entityManager;
    }

    /**
     * @param Customer|object|null $customer
     * @param float $subtotal
     * @param string $status
     * @return Order
     */
    protected function createOrder($customer = null, $subtotal = 10.1, $status = 'complete')
    {
        $order = new Order();
        $order->setId(1);
        if ($customer) {
            $order->setCustomer($customer);
        }
        $order->setSubtotalAmount($subtotal);
        $order->setStatus($status);

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
