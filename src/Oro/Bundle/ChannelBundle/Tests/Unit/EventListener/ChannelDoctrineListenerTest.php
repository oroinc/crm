<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Async\Topic\LifetimeHistoryStatusUpdateTopic;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use Oro\Bundle\ChannelBundle\Entity\Manager\LifetimeHistoryStatusUpdateManager;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeHistoryRepository;
use Oro\Bundle\ChannelBundle\EventListener\ChannelDoctrineListener;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Repository\CustomerRepository;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\ORM\OrmTestCase;

class ChannelDoctrineListenerTest extends OrmTestCase
{
    private const TEST_CHANNEL_ID = 1;
    private const TEST_ACCOUNT_ID = 112;

    /** @var LifetimeHistoryRepository */
    private $lifetimeRepo;

    /** @var CustomerRepository */
    private $customerRepo;

    /** @var EntityManager */
    private $em;

    /** @var UnitOfWork */
    private $uow;

    /** @var ChannelDoctrineListener */
    private $channelDoctrineListener;

    private array $settings = [
        'someChannelType' => [
            'entity' => Customer::class,
            'field'  => 'lifetime',
        ]
    ];

    /** @var SettingsProvider */
    private $settingsProvider;

    /** @var MessageProducerInterface */
    private $messageProducer;
    /** @var LifetimeHistoryStatusUpdateManager */
    private $statusUpdateManager;

    protected function setUp(): void
    {
        $this->lifetimeRepo = $this->createMock(LifetimeHistoryRepository::class);
        $this->customerRepo = $this->createMock(CustomerRepository::class);
        $this->uow = $this->createMock(UnitOfWork::class);

        $this->em = $this->createMock(EntityManager::class);
        $this->em->expects(self::any())
            ->method('getUnitOfWork')
            ->willReturn($this->uow);

        $this->settingsProvider = $this->createMock(SettingsProvider::class);
        $this->settingsProvider->expects(self::any())
            ->method('getLifetimeValueSettings')
            ->willReturn($this->settings);

        $this->messageProducer = $this->createMock(MessageProducerInterface::class);
    }

    protected function setUpDoctrineListener(bool $useQueue = true): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects(self::any())
            ->method('getRepository')
            ->with(LifetimeValueHistory::class)
            ->willReturn($this->lifetimeRepo);
        $this->statusUpdateManager = new LifetimeHistoryStatusUpdateManager(
            $managerRegistry,
            $this->messageProducer
        );
        $this->statusUpdateManager->setUseQueue($useQueue);
        $this->channelDoctrineListener = new ChannelDoctrineListener(
            $this->settingsProvider,
            $this->statusUpdateManager
        );
    }

    public function testOnFlush()
    {
        $this->setUpDoctrineListener();

        $args = new OnFlushEventArgs($this->em);

        $this->em->expects(self::any())
            ->method('getRepository')
            ->withConsecutive(
                ['OroSalesBundle:Customer'],
                ['OroChannelBundle:LifetimeValueHistory']
            )->willReturnOnConsecutiveCalls(
                $this->customerRepo,
                $this->lifetimeRepo
            );

        $account = $this->createMock(Account::class);
        $account->expects(self::any())
            ->method('getId')
            ->willReturn(self::TEST_ACCOUNT_ID);
        $channel = $this->createMock(Channel::class);
        $channel->expects(self::any())
            ->method('getId')
            ->willReturn(self::TEST_CHANNEL_ID);

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

        $this->uow->expects(self::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn($entities);
        $this->uow->expects(self::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([]);
        $this->uow->expects(self::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([]);
        $this->uow->expects(self::once())
            ->method('getScheduledCollectionDeletions')
            ->willReturn([]);
        $this->uow->expects(self::once())
            ->method('getScheduledCollectionUpdates')
            ->willReturn([]);

        $channelDoctrineListener = new ChannelDoctrineListener($this->settingsProvider, $this->statusUpdateManager);

        $channelDoctrineListener->onFlush($args);

        $queued = ReflectionUtil::getPropertyValue($channelDoctrineListener, 'queued');
        foreach ($queued as $entity => $value) {
            self::assertEquals(Customer::class, $entity);

            foreach ($value as $changeSet) {
                self::assertArrayHasKey('account', $changeSet);
                self::assertArrayHasKey('channel', $changeSet);
                self::assertEquals(self::TEST_ACCOUNT_ID, $changeSet['account']);
                self::assertEquals(self::TEST_CHANNEL_ID, $changeSet['channel']);
            }
        }

        return $channelDoctrineListener;
    }

    /**
     * @dataProvider useQueueDataProvider
     * @param string $sapiName
     * @return void
     */
    public function testPostFlush(bool $useQueue)
    {
        $this->setUpDoctrineListener($useQueue);
        $lifetimeAmounts = [100, 200];
        $this->em->expects(self::any())
            ->method('getRepository')
            ->with(LifetimeValueHistory::class)
            ->willReturn($this->lifetimeRepo);

        $account = new Account();
        ReflectionUtil::setId($account, 1);
        $channel = new Channel();
        ReflectionUtil::setId($channel, 2);
        $account2 = clone $account;
        ReflectionUtil::setId($account2, 3);

        $this->lifetimeRepo->expects(self::exactly(2))
            ->method('calculateAccountLifetime')
            ->with(
                [Customer::class => 'lifetime'],
                self::isInstanceOf(Account::class),
                self::isInstanceOf(Channel::class)
            )
            ->willReturnOnConsecutiveCalls(... $lifetimeAmounts);

        $this->em->expects(self::exactly(2))
            ->method('persist');
        $this->em->expects(self::once())
            ->method('flush');

        $records = [
            [1, 2, null],
            [3, 2, null],
        ];
        $status = LifetimeValueHistory::STATUS_OLD;

        if ($useQueue) {
            $this->messageProducer->expects(self::once())
                ->method('send')
                ->with(
                    LifetimeHistoryStatusUpdateTopic::getName(),
                    [
                        LifetimeHistoryStatusUpdateTopic::RECORDS_FIELD => $records,
                        LifetimeHistoryStatusUpdateTopic::STATUS_FIELD => $status,
                    ]
                );
        } else {
            $this->lifetimeRepo->expects(self::once())
                ->method('massStatusUpdate')
                ->withAnyParameters();
        }

        $channelDoctrineListener = new ChannelDoctrineListener($this->settingsProvider, $this->statusUpdateManager);

        ReflectionUtil::setPropertyValue($channelDoctrineListener, 'queued', [
            uniqid('accountId__channelId', true) => ['account' => $account, 'channel' => $channel],
            uniqid('accountId__channelId', true) => ['account' => $account2, 'channel' => $channel],
        ]);

        $channelDoctrineListener->postFlush(new PostFlushEventArgs($this->em));
    }

    public function testScheduleEntityUpdate()
    {
        $this->setUpDoctrineListener();
        $account = $this->createMock(Account::class);
        $channel = $this->createMock(Channel::class);
        $customer = new \stdClass();

        $channelDoctrineListener = new ChannelDoctrineListener($this->settingsProvider, $this->statusUpdateManager);

        $this->uow->expects(self::exactly(2))
            ->method('isScheduledForDelete')
            ->willReturn(false);
        $channelDoctrineListener->initializeFromEventArgs(new PostFlushEventArgs($this->em));

        $channelDoctrineListener->scheduleEntityUpdate($customer, $account, $channel);

        self::assertNotEmpty(ReflectionUtil::getPropertyValue($channelDoctrineListener, 'queued'));
    }

    public function testScheduleEntityUpdateFailed()
    {
        $this->setUpDoctrineListener();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('UOW is missing, listener is not initialized');

        $account = $this->createMock(Account::class);
        $channel = $this->createMock(Channel::class);
        $customer = new \stdClass();

        $this->channelDoctrineListener->scheduleEntityUpdate($customer, $account, $channel);
    }

    public function useQueueDataProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
