<?php

namespace Oro\Bundle\ActivityContactBundle\Tests\Unit\EventListener;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\ActivityBundle\Event\ActivityEvent;
use Oro\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use Oro\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use Oro\Bundle\ActivityContactBundle\EventListener\ActivityListener;
use Oro\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;
use Oro\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestActivity;
use Oro\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestDirectionProvider;
use Oro\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestTarget;
use Oro\Bundle\ActivityContactBundle\Tests\Unit\Stub\AccountStub as Account;
use Oro\Bundle\ActivityContactBundle\Tests\Unit\Stub\EmailStub as Email;
use Oro\Bundle\ActivityContactBundle\Tools\ActivityListenerChangedTargetsBag;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\TestContainerBuilder;

class ActivityListenerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var ConfigInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $config;

    /** @var TestTarget */
    private $testTarget;

    /** @var \DateTime */
    private $testDate;

    /** @var ActivityListener */
    private $listener;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->config = $this->createMock(ConfigInterface::class);

        $providers = TestContainerBuilder::create()
            ->add(TestActivity::class, new TestDirectionProvider())
            ->add(Email::class, new TestDirectionProvider())
            ->getContainer($this);

        $configProvider = $this->createMock(ConfigProvider::class);
        $configProvider->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->config);

        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->any())
            ->method('getProvider')
            ->willReturn($configProvider);

        $this->listener = new ActivityListener(
            new ActivityContactProvider([TestActivity::class, Email::class], $providers),
            $this->doctrineHelper,
            $configManager
        );
        $this->listener->setChangedTargetsBag(new ActivityListenerChangedTargetsBag($this->doctrineHelper));
    }

    /**
     * @dataProvider onRemoveActivityDataProvider
     */
    public function testOnRemoveActivity(string $direction, bool $extend, ?int $expected): void
    {
        $target = new TestTarget();
        $activity = new TestActivity($direction, new \DateTime());

        $this->config->expects($this->any())
            ->method('is')
            ->with('is_extend')
            ->willReturn($extend);

        $event = new ActivityEvent($activity, $target);
        $this->listener->onRemoveActivity($event);

        $this->assertEquals($expected, $target->ac_contact_count);
    }

    public function onRemoveActivityDataProvider(): array
    {
        return [
            'Direction unknown' => [
                'activity' => DirectionProviderInterface::DIRECTION_UNKNOWN,
                'extend' => false,
                'expected' => null
            ],
            'Direction incoming and target excluded' => [
                'activity' => DirectionProviderInterface::DIRECTION_INCOMING,
                'extend' => false,
                'expected' => null
            ],
            'Direction incoming and target not excluded' => [
                'activity' => DirectionProviderInterface::DIRECTION_INCOMING,
                'extend' => true,
                'expected' => -1
            ],
        ];
    }

    /**
     * @dataProvider onAddActivityProvider
     */
    public function testOnAddActivity(object $object, string $expectedDirection)
    {
        $this->testTarget = new TestTarget();
        $event = new ActivityEvent($object, $this->testTarget);

        $this->config->expects($this->once())
            ->method('is')
            ->with('is_extend')
            ->willReturn(true);

        $this->listener->onAddActivity($event);

        $accessor = PropertyAccess::createPropertyAccessor();
        switch ($expectedDirection) {
            case DirectionProviderInterface::DIRECTION_INCOMING:
                $this->assertEquals(1, $accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT));
                $this->assertEquals(1, $accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT_IN));
                $this->assertNull($accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT_OUT));
                $this->assertInstanceOf(
                    \DateTime::class,
                    $accessor->getValue($this->testTarget, ActivityScope::LAST_CONTACT_DATE)
                );
                $this->assertInstanceOf(
                    \DateTime::class,
                    $accessor->getValue($this->testTarget, ActivityScope::LAST_CONTACT_DATE_IN)
                );
                $this->assertNull($accessor->getValue($this->testTarget, ActivityScope::LAST_CONTACT_DATE_OUT));
                break;
            case DirectionProviderInterface::DIRECTION_OUTGOING:
                $this->assertEquals(1, $accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT));
                $this->assertEquals(1, $accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT_OUT));
                $this->assertNull($accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT_IN));
                $this->assertInstanceOf(
                    \DateTime::class,
                    $accessor->getValue($this->testTarget, ActivityScope::LAST_CONTACT_DATE)
                );
                $this->assertInstanceOf(
                    \DateTime::class,
                    $accessor->getValue($this->testTarget, ActivityScope::LAST_CONTACT_DATE_OUT)
                );
                $this->assertNull($accessor->getValue($this->testTarget, ActivityScope::LAST_CONTACT_DATE_IN));
                break;
            default:
                $this->assertNull($accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT));
                $this->assertNull($accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT_OUT));
                $this->assertNull($accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT_IN));
                $this->assertNull($accessor->getValue($this->testTarget, ActivityScope::LAST_CONTACT_DATE));
                $this->assertNull($accessor->getValue($this->testTarget, ActivityScope::LAST_CONTACT_DATE_OUT));
                $this->assertNull($accessor->getValue($this->testTarget, ActivityScope::LAST_CONTACT_DATE));
        }
    }

    public function testOnAddActivityWithNonExtendedEntity()
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $this->testTarget = new TestTarget();
        $this->testDate = new \DateTime();
        $object = new TestActivity(DirectionProviderInterface::DIRECTION_INCOMING, $this->testDate);
        $event = new ActivityEvent($object, $this->testTarget);

        $this->config->expects($this->once())
            ->method('is')
            ->with('is_extend')
            ->willReturn(false);

        $this->listener->onAddActivity($event);
        $this->assertNull($accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT));
    }

    public function onAddActivityProvider(): array
    {
        $this->testDate = new \DateTime();

        return [
            'incoming'    => [
                new TestActivity(DirectionProviderInterface::DIRECTION_INCOMING, $this->testDate),
                DirectionProviderInterface::DIRECTION_INCOMING
            ],
            'outgoing'    => [
                new TestActivity(DirectionProviderInterface::DIRECTION_OUTGOING, $this->testDate),
                DirectionProviderInterface::DIRECTION_OUTGOING
            ],
            'badActivity' => [
                new \stdClass(),
                DirectionProviderInterface::DIRECTION_UNKNOWN
            ]
        ];
    }

    public function testFlushEvents()
    {
        $targets = [
            $this->getEntity(Account::class, ['id' => 2, 'createdAt' => new \DateTime()]),
            $this->getEntity(Account::class, ['id' => 3, 'createdAt' => new \DateTime()]),
            $this->getEntity(Account::class, ['id' => 4, 'createdAt' => new \DateTime()]),
        ];

        $changedTarget = $targets[2];

        $changeSets = [
            2 => ['ac_last_contact_date' => [new \DateTime(), new \DateTime()]],
            3 => ['ac_last_contact_date' => [new \DateTime(), new \DateTime()]],
            4 => [
                'ac_last_contact_date' => [new \DateTime(), new \DateTime()],
                'ac_contact_count' => [1, 2],
                'ac_contact_count_in' => [0, 1],
            ],
        ];

        $activity = $this->getEntity(Email::class, ['id' => 1]);
        $activity->setActivityTargets($targets);
        $activity->setCreated(new \DateTime());
        $em = $this->createMock(EntityManager::class);
        $uow = $this->createMock(UnitOfWork::class);

        $onFlushEvent = $this->createMock(OnFlushEventArgs::class);
        $onFlushEvent->expects($this->any())
            ->method('getEntityManager')
            ->willReturn($em);
        $em->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($uow);
        $uow->expects(self::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn(array_merge([$activity], $targets));
        $uow->expects(self::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([]);
        $uow->expects($this->any())
            ->method('getEntityChangeSet')
            ->willReturnCallback(function ($entity) use ($changeSets) {
                return $changeSets[$entity->getId()] ?? [];
            });
        $this->doctrineHelper->expects($this->any())
            ->method('getEntityIdentifierFieldNamesForClass')
            ->willReturnCallback(fn ($class) => Email::class === $class ? [true] : []);
        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->willReturnCallback(fn ($entity) => $entity::class);
        $this->doctrineHelper->expects($this->any())
            ->method('getSingleEntityIdentifier')
            ->willReturnCallback(fn ($entity) => $entity->getId());
        $this->config->expects(self::once())
            ->method('is')
            ->with('is_extend', true)
            ->willReturn(true);

        $em2 = $this->createMock(EntityManager::class);
        $accountRepository = $this->createMock(ServiceEntityRepository::class);
        $postFlushEvent = $this->createMock(PostFlushEventArgs::class);
        $postFlushEvent->expects($this->any())
            ->method('getEntityManager')
            ->willReturn($em2);
        $em2->expects(self::once())
            ->method('persist')
            ->with($changedTarget);
        $em2->expects(self::once())
            ->method('getRepository')
            ->with(Account::class)
            ->willReturn($accountRepository);
        $accountRepository->expects(self::once())
            ->method('find')
            ->with($changedTarget->getId())
            ->willReturn($changedTarget);

        $this->listener->onFlush($onFlushEvent);
        $this->listener->postFlush($postFlushEvent);
    }
}
