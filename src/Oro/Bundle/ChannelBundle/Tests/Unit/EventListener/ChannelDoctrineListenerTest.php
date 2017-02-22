<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\OrmTestCase;
use Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer;
use Oro\Bundle\ChannelBundle\EventListener\ChannelDoctrineListener;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeHistoryRepository;
use Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository;
use Oro\Bundle\MagentoBundle\Entity\Customer as CustomerEntity;

class ChannelDoctrineListenerTest extends OrmTestCase
{
    const TEST_CHANNEL_ID = 1;
    const TEST_ACCOUNT_ID = 112;

    /** @var LifetimeHistoryRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $lifetimeRepo;

    /** @var CustomerRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerRepo;

    /** @var EntityManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var UnitOfWork|\PHPUnit_Framework_MockObject_MockObject */
    protected $uow;

    /** @var ChannelDoctrineListener */
    protected $channelDoctrineListener;

    /** @var array */
    protected $settings = [
        'someChannelType' => [
            'entity' => 'Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer',
            'field'  => 'lifetime',
        ]
    ];

    protected function setUp()
    {
        $this->lifetimeRepo = $this
            ->getMockBuilder('Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeHistoryRepository')
            ->disableOriginalConstructor()->getMock();

        $this->customerRepo = $this
            ->getMockBuilder('Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository')
            ->disableOriginalConstructor()->getMock();


        $this->uow = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()->getMock();
        $this->em  = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();
        $this->em->expects($this->any())->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));

        $settingProvider = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();
        $settingProvider->expects($this->once())->method('getLifetimeValueSettings')
            ->will($this->returnValue($this->settings));

        $this->channelDoctrineListener = new ChannelDoctrineListener($settingProvider);
    }

    protected function tearDown()
    {
        unset($this->em, $this->uow, $this->channelDoctrineListener);
    }

    public function testOnFlush()
    {
        $args = new OnFlushEventArgs($this->em);

        $this->em->expects($this->any())->method('getRepository')
            ->withConsecutive(
                [$this->equalTo('OroSalesBundle:Customer')],
                [$this->equalTo('OroChannelBundle:LifetimeValueHistory')]
            )->willReturnOnConsecutiveCalls(
                $this->returnValue($this->customerRepo),
                $this->returnValue($this->lifetimeRepo)
            );

        $account = $this->createMock('Oro\Bundle\AccountBundle\Entity\Account');
        $account->expects($this->any())->method('getId')
            ->will($this->returnValue(self::TEST_ACCOUNT_ID));
        $channel = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');
        $channel->expects($this->any())->method('getId')
            ->will($this->returnValue(self::TEST_CHANNEL_ID));

        $customer = new Customer();
        $customer->setAccount($account);
        $customer->setDataChannel($channel);
        $customer->setId(1);

        $customer1 = clone $customer;
        $customer1->setId(2);

        $entities = [
            'hash1' => $customer,
            'hash2' => $customer1,
        ];

        $this->uow->expects($this->once())->method('getScheduledEntityInsertions')
            ->will($this->returnValue($entities));
        $this->uow->expects($this->once())->method('getScheduledEntityDeletions')
            ->will($this->returnValue([]));
        $this->uow->expects($this->once())->method('getScheduledEntityUpdates')
            ->will($this->returnValue([]));
        $this->uow->expects($this->once())->method('getScheduledCollectionDeletions')
            ->will($this->returnValue([]));
        $this->uow->expects($this->once())->method('getScheduledCollectionUpdates')
            ->will($this->returnValue([]));

        $this->channelDoctrineListener->onFlush($args);

        $queued = $this->readAttribute($this->channelDoctrineListener, 'queued');
        foreach ($queued as $entity => $value) {
            $this->assertEquals($entity, 'Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer');

            foreach ($value as $changeSet) {
                $this->assertArrayHasKey('account', $changeSet);
                $this->assertArrayHasKey('channel', $changeSet);
                $this->assertEquals($changeSet['account'], self::TEST_ACCOUNT_ID);
                $this->assertEquals($changeSet['channel'], self::TEST_CHANNEL_ID);
            }
        }

        return $this->channelDoctrineListener;
    }

    public function testPostFlush()
    {
        $args = new PostFlushEventArgs($this->em);

        $this->em->expects($this->any())->method('getRepository')
            ->with($this->equalTo('OroChannelBundle:LifetimeValueHistory'))
            ->will($this->returnValue($this->lifetimeRepo));

        $account = $this->createMock('Oro\Bundle\AccountBundle\Entity\Account');
        $channel = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');
        $channel->expects($this->any())->method('getId')->will($this->returnValue(1));
        $account2 = clone $account;

        $queue = [
            uniqid('accountId__channelId', true) => ['account' => $account, 'channel' => $channel],
            uniqid('accountId__channelId', true) => ['account' => $account2, 'channel' => $channel],
        ];

        $this->lifetimeRepo->expects($this->exactly(2))->method('calculateAccountLifetime')
            ->with(
                ['Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer' => 'lifetime'],
                $this->isInstanceOf('Oro\Bundle\AccountBundle\Entity\Account'),
                $this->isInstanceOf('Oro\Bundle\ChannelBundle\Entity\Channel')
            )
            ->will($this->onConsecutiveCalls(100, 200));

        $this->em->expects($this->exactly(2))->method('persist');
        $this->em->expects($this->once())->method('flush');

        $reflectionProperty = new \ReflectionProperty(get_class($this->channelDoctrineListener), 'queued');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->channelDoctrineListener, $queue);

        $this->channelDoctrineListener->postFlush($args);
    }

    public function testScheduleEntityUpdate()
    {
        $account = $this->createMock('Oro\Bundle\AccountBundle\Entity\Account');
        $channel = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');

        $customer = new CustomerEntity();
        $customer
            ->setAccount($account)
            ->setDataChannel($channel);

        $this->uow->expects($this->exactly(2))->method('isScheduledForDelete')->willReturn(false);

        $reflectionProperty = new \ReflectionProperty(get_class($this->channelDoctrineListener), 'uow');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->channelDoctrineListener, $this->uow);

        $this->assertAttributeEmpty('queued', $this->channelDoctrineListener);
        $this->channelDoctrineListener->scheduleEntityUpdate($customer, $account, $channel);
        $this->assertAttributeNotEmpty('queued', $this->channelDoctrineListener);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage UOW is missing, listener is not initialized
     */
    public function testScheduleEntityUpdateFailed()
    {
        $account = $this->createMock('Oro\Bundle\AccountBundle\Entity\Account');
        $channel = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');

        $customer = new CustomerEntity();
        $customer
            ->setAccount($account)
            ->setDataChannel($channel);

        $this->channelDoctrineListener->scheduleEntityUpdate($customer, $account, $channel);
    }
}
