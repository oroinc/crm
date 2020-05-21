<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\ChannelBundle\EventListener\ChannelDoctrineListener;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\EventListener\OrderListener;

class OrderListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ChannelDoctrineListener
     */
    protected $listener;

    protected function setUp(): void
    {
        $this->listener = $this->getMockBuilder('Oro\Bundle\ChannelBundle\EventListener\ChannelDoctrineListener')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param Order $order
     * @param float $expectedLifetime
     * @dataProvider prePersistDataProvider
     */
    public function testPrePersist($order, $expectedLifetime = null)
    {
        $entityManager = $this->createEntityManagerMock();
        if ($expectedLifetime) {
            $this->customerRepository->expects($this->once())
                ->method('updateCustomerLifetimeValue')
                ->with($this->isInstanceOf('Oro\Bundle\MagentoBundle\Entity\Customer'), $expectedLifetime);
        } else {
            $this->customerRepository->expects($this->never())->method('updateCustomerLifetimeValue');
        }

        $listener = new OrderListener($this->listener);
        $listener->prePersist($order, new LifecycleEventArgs($order, $entityManager));
    }

    /**
     * @return array
     */
    public function prePersistDataProvider()
    {
        return [
            'zero subtotal'      => [$this->createOrder(null, 0)],
            'no customer'        => [$this->createOrder(null)],
            'incorrect customer' => [$this->createOrder(new \DateTime())],
            'equal lifetime'     => [$this->createOrder($this->createCustomer(), 10), 10],
            'updated lifetime'   => [$this->createOrder($this->createCustomer(), 15), 15],
            'canceled order'     => [$this->createOrder($this->createCustomer(), 3, Order::STATUS_CANCELED)]
        ];
    }

    /**
     * @param Order $order
     * @param array $changeSet
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

        $listener = new class($this->listener) extends OrderListener {
            public function xgetOrdersForUpdate(): array
            {
                return $this->ordersForUpdate;
            }
        };

        $listener->preUpdate($order, new PreUpdateEventArgs($order, $entityManager, $changeSet));

        if ($isUpdateRequired) {
            static::assertEquals([$order->getId() => true], $listener->xgetOrdersForUpdate());
        } else {
            static::assertEmpty($listener->xgetOrdersForUpdate());
        }
    }

    /**
     * @return array
     */
    public function preUpdateDataProvider()
    {
        return [
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
            $entityManager = $this->createEntityManagerMock($order);
        } else {
            $entityManager = $this->createEntityManagerMock();
        }

        $listener = new OrderListener($this->listener);

        if ($newLifetime) {
            $reflectionProperty = new \ReflectionProperty(get_class($listener), 'ordersForUpdate');
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($listener, [1 => true]);
        }

        if ($newLifetime) {
            $this->customerRepository->expects($this->once())
                ->method('updateCustomerLifetimeValue')
                ->with($this->isInstanceOf('Oro\Bundle\MagentoBundle\Entity\Customer'), $newLifetime);
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
     * @throws \PHPUnit\Framework\Exception
     */
    protected function createEntityManagerMock($order = null)
    {
        $this->customerRepository = $this
            ->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\Repository\CustomerRepository')
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
            ->will($this->returnValue(['Oro\Bundle\MagentoBundle\Entity\Order' => $order]));
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

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'getUnitOfWork', 'flush'])
            ->getMock();
        $entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with('OroMagentoBundle:Customer')
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

    public function testScheduleLifetimeValueHistory()
    {
        $expectedLifetime = 200;
        $order = new Order();

        $account = $this->createMock('Oro\Bundle\AccountBundle\Entity\Account');
        $channel = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');

        $customer = new Customer();
        $customer
            ->setAccount($account)
            ->setDataChannel($channel);

        $order
            ->setCustomer($customer)
            ->setSubtotalAmount($expectedLifetime);

        $entityManager = $this->createEntityManagerMock();
        $this->customerRepository->expects($this->once())
            ->method('updateCustomerLifetimeValue')
            ->with($this->isInstanceOf('Oro\Bundle\MagentoBundle\Entity\Customer'), $expectedLifetime);

        $this->listener->expects($this->once())
            ->method('scheduleEntityUpdate')
            ->with($this->equalTo($customer), $this->equalTo($account), $this->equalTo($channel));

        $listener = new OrderListener($this->listener);
        $listener->prePersist($order, new LifecycleEventArgs($order, $entityManager));
    }

    public function testNotScheduleLifetimeValueHistoryWithoutAccount()
    {
        $expectedLifetime = 200;
        $order = new Order();
        $customer = new Customer();
        $order
            ->setCustomer($customer)
            ->setSubtotalAmount($expectedLifetime);

        $entityManager = $this->createEntityManagerMock();
        $this->customerRepository->expects($this->once())
            ->method('updateCustomerLifetimeValue')
            ->with($this->isInstanceOf('Oro\Bundle\MagentoBundle\Entity\Customer'), $expectedLifetime);

        $this->listener->expects($this->never())->method('scheduleEntityUpdate');

        $listener = new OrderListener($this->listener);
        $listener->prePersist($order, new LifecycleEventArgs($order, $entityManager));
    }
}
