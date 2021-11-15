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
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\TestUtils\ORM\OrmTestCase;

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

        $this->channelDoctrineListener = new ChannelDoctrineListener($this->settingsProvider);
    }

    public function testOnFlush()
    {
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

        $channelDoctrineListener = new ChannelDoctrineListener($this->settingsProvider);

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

    public function testPostFlush()
    {
        $this->em->expects(self::any())
            ->method('getRepository')
            ->with('OroChannelBundle:LifetimeValueHistory')
            ->willReturn($this->lifetimeRepo);

        $account = $this->createMock(Account::class);
        $channel = $this->createMock(Channel::class);
        $channel->expects(self::any())
            ->method('getId')
            ->willReturn(1);
        $account2 = clone $account;

        $this->lifetimeRepo->expects(self::exactly(2))
            ->method('calculateAccountLifetime')
            ->with(
                [Customer::class => 'lifetime'],
                self::isInstanceOf(Account::class),
                self::isInstanceOf(Channel::class)
            )
            ->willReturnOnConsecutiveCalls(100, 200);

        $this->em->expects(self::exactly(2))
            ->method('persist');
        $this->em->expects(self::once())
            ->method('flush');

        $channelDoctrineListener = new ChannelDoctrineListener($this->settingsProvider);

        ReflectionUtil::setPropertyValue($channelDoctrineListener, 'queued', [
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

        $channelDoctrineListener = new ChannelDoctrineListener($this->settingsProvider);

        $this->uow->expects(self::exactly(2))
            ->method('isScheduledForDelete')
            ->willReturn(false);
        $channelDoctrineListener->initializeFromEventArgs(new PostFlushEventArgs($this->em));

        $channelDoctrineListener->scheduleEntityUpdate($customer, $account, $channel);

        self::assertNotEmpty(ReflectionUtil::getPropertyValue($channelDoctrineListener, 'queued'));
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
