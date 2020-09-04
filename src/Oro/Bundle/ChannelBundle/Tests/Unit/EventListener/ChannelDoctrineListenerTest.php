<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeHistoryRepository;
use Oro\Bundle\ChannelBundle\EventListener\ChannelDoctrineListener;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository;
use Oro\Component\TestUtils\ORM\OrmTestCase;

class ChannelDoctrineListenerTest extends OrmTestCase
{
    private const TEST_CHANNEL_ID = 1;
    private const TEST_ACCOUNT_ID = 112;

    /** @var LifetimeHistoryRepository */
    protected $lifetimeRepo;

    /** @var CustomerRepository */
    protected $customerRepo;

    /** @var EntityManager */
    protected $em;

    /** @var UnitOfWork */
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

    /** @var SettingsProvider  */
    private $settingsProvider;

    protected function setUp(): void
    {
        $this->lifetimeRepo = $this->getMockBuilder(LifetimeHistoryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerRepo = $this->getMockBuilder(CustomerRepository::class)->disableOriginalConstructor()->getMock();

        $this->uow = $this->getMockBuilder(UnitOfWork::class)->disableOriginalConstructor()->getMock();

        $this->em  = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->em->expects(static::any())
            ->method('getUnitOfWork')
            ->willReturn($this->uow);

        $this->settingsProvider = $this->getMockBuilder(SettingsProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->settingsProvider->expects(static::any())
            ->method('getLifetimeValueSettings')
            ->willReturn($this->settings);

        $this->channelDoctrineListener = new ChannelDoctrineListener($this->settingsProvider);
    }

    protected function tearDown(): void
    {
        unset($this->em, $this->uow, $this->channelDoctrineListener);
    }

    public function testOnFlush()
    {
        $args = new OnFlushEventArgs($this->em);

        $this->em->expects(static::any())->method('getRepository')
            ->withConsecutive(
                ['OroSalesBundle:Customer'],
                ['OroChannelBundle:LifetimeValueHistory']
            )->willReturnOnConsecutiveCalls(
                $this->customerRepo,
                $this->lifetimeRepo
            );

        $account = $this->createMock(Account::class);
        $account->expects(static::any())->method('getId')->willReturn(self::TEST_ACCOUNT_ID);
        $channel = $this->createMock(Channel::class);
        $channel->expects(static::any())->method('getId')->willReturn(self::TEST_CHANNEL_ID);

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

        $this->uow->expects(static::once())->method('getScheduledEntityInsertions')->willReturn($entities);
        $this->uow->expects(static::once())->method('getScheduledEntityDeletions')->willReturn([]);
        $this->uow->expects(static::once())->method('getScheduledEntityUpdates')->willReturn([]);
        $this->uow->expects(static::once())->method('getScheduledCollectionDeletions')->willReturn([]);
        $this->uow->expects(static::once())->method('getScheduledCollectionUpdates')->willReturn([]);

        $channelDoctrineListener = new class($this->settingsProvider) extends ChannelDoctrineListener {
            public function xgetQueued(): array
            {
                return $this->queued;
            }
        };

        $channelDoctrineListener->onFlush($args);

        foreach ($channelDoctrineListener->xgetQueued() as $entity => $value) {
            static::assertEquals(Customer::class, $entity);

            foreach ($value as $changeSet) {
                static::assertArrayHasKey('account', $changeSet);
                static::assertArrayHasKey('channel', $changeSet);
                static::assertEquals(self::TEST_ACCOUNT_ID, $changeSet['account']);
                static::assertEquals(self::TEST_CHANNEL_ID, $changeSet['channel']);
            }
        }

        return $channelDoctrineListener;
    }

    public function testPostFlush()
    {
        $this->em->expects(static::any())->method('getRepository')
            ->with('OroChannelBundle:LifetimeValueHistory')
            ->willReturn($this->lifetimeRepo);

        $account = $this->createMock(Account::class);
        $channel = $this->createMock(Channel::class);
        $channel->expects(static::any())->method('getId')->willReturn(1);
        $account2 = clone $account;

        $this->lifetimeRepo->expects(static::exactly(2))
            ->method('calculateAccountLifetime')
            ->with(
                [Customer::class => 'lifetime'],
                static::isInstanceOf(Account::class),
                static::isInstanceOf(Channel::class)
            )
            ->willReturnOnConsecutiveCalls(100, 200);

        $this->em->expects(static::exactly(2))->method('persist');
        $this->em->expects(static::once())->method('flush');

        $channelDoctrineListener = new class($this->settingsProvider) extends ChannelDoctrineListener {
            public function xsetQueued(array $queued): void
            {
                $this->queued = $queued;
            }
        };

        $channelDoctrineListener->xsetQueued([
            uniqid('accountId__channelId', true) => ['account' => $account, 'channel' => $channel],
            uniqid('accountId__channelId', true) => ['account' => $account2, 'channel' => $channel],
        ]);

        $channelDoctrineListener->postFlush(new PostFlushEventArgs($this->em));
    }

    public function testScheduleEntityUpdate()
    {
        $account = $this->createMock(Account::class);
        $channel = $this->createMock(Channel::class);
        $customer = new \stdClass();

        $channelDoctrineListener = new class($this->settingsProvider) extends ChannelDoctrineListener {
            public function xgetQueued(): array
            {
                return $this->queued;
            }
        };

        $this->uow->expects(static::exactly(2))->method('isScheduledForDelete')->willReturn(false);
        $channelDoctrineListener->initializeFromEventArgs(new PostFlushEventArgs($this->em));

        $channelDoctrineListener->scheduleEntityUpdate($customer, $account, $channel);

        static::assertNotEmpty($channelDoctrineListener->xgetQueued());
    }

    public function testScheduleEntityUpdateFailed()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('UOW is missing, listener is not initialized');

        $account = $this->createMock(Account::class);
        $channel = $this->createMock(Channel::class);
        $customer = new \stdClass();

        $this->channelDoctrineListener->scheduleEntityUpdate($customer, $account, $channel);
    }
}
