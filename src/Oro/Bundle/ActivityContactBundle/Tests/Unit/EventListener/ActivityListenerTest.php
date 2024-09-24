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
use Oro\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestTarget;
use Oro\Bundle\ActivityContactBundle\Tests\Unit\Stub\AccountStub as Account;
use Oro\Bundle\ActivityContactBundle\Tests\Unit\Stub\EmailStub as Email;
use Oro\Bundle\ActivityContactBundle\Tools\ActivityListenerChangedTargetsBag;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Component\Testing\ReflectionUtil;

class ActivityListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var ConfigInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $config;

    /** @var ActivityListener */
    private $listener;

    /** @var ActivityContactProvider */
    private $activityContactProvider;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->config = $this->createMock(ConfigInterface::class);
        $this->activityContactProvider = $this->createMock(ActivityContactProvider::class);

        $testActivityDirectionProvider = $this->createMock(DirectionProviderInterface::class);
        $testActivityDirectionProvider->expects(self::any())
            ->method('getDirection')
            ->willReturnCallback(function (TestActivity $activity) {
                return $activity->getDirection();
            });
        $testActivityDirectionProvider->expects(self::any())
            ->method('getDate')
            ->willReturnCallback(function (TestActivity $activity) {
                return $activity->getCreated();
            });
        $testActivityDirectionProvider->expects(self::any())
            ->method('getLastActivitiesDateForTarget')
            ->willReturnCallback(function (EntityManager $em, Account $target) {
                return [
                    'all'       => $target->getCreated(),
                    'direction' => $target->getCreated()
                ];
            });

        $emailDirectionProvider = $this->createMock(DirectionProviderInterface::class);
        $emailDirectionProvider->expects(self::any())
            ->method('getDirection')
            ->willReturnCallback(function (Email $activity) {
                return $activity->getDirection();
            });
        $emailDirectionProvider->expects(self::any())
            ->method('getDate')
            ->willReturnCallback(function (Email $activity) {
                return $activity->getCreated();
            });
        $emailDirectionProvider->expects(self::any())
            ->method('getLastActivitiesDateForTarget')
            ->willReturnCallback(function (EntityManager $em, Account $target) {
                return [
                    'all'       => $target->getCreated(),
                    'direction' => $target->getCreated()
                ];
            });

        $configProvider = $this->createMock(ConfigProvider::class);
        $configProvider->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->config);

        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->any())
            ->method('getProvider')
            ->willReturn($configProvider);

        $this->activityContactProvider->expects($this->any())
            ->method('getActivityDirectionProvider')
            ->willReturn($testActivityDirectionProvider);

        $this->listener = new ActivityListener(
            $this->activityContactProvider,
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
        $target = new TestTarget();
        $event = new ActivityEvent($object, $target);

        $this->config->expects($this->once())
            ->method('is')
            ->with('is_extend')
            ->willReturn(true);

        $this->activityContactProvider->expects($this->any())
            ->method('getActivityDirection')
            ->willReturn($expectedDirection);

        $this->activityContactProvider->expects($this->any())
            ->method('getActivityDate')
            ->willReturn(new \DateTime());

        $this->listener->onAddActivity($event);

        $accessor = PropertyAccess::createPropertyAccessor();
        switch ($expectedDirection) {
            case DirectionProviderInterface::DIRECTION_INCOMING:
                $this->assertEquals(1, $accessor->getValue($target, ActivityScope::CONTACT_COUNT));
                $this->assertEquals(1, $accessor->getValue($target, ActivityScope::CONTACT_COUNT_IN));
                $this->assertNull($accessor->getValue($target, ActivityScope::CONTACT_COUNT_OUT));
                $this->assertInstanceOf(
                    \DateTime::class,
                    $accessor->getValue($target, ActivityScope::LAST_CONTACT_DATE)
                );
                $this->assertInstanceOf(
                    \DateTime::class,
                    $accessor->getValue($target, ActivityScope::LAST_CONTACT_DATE_IN)
                );
                $this->assertNull($accessor->getValue($target, ActivityScope::LAST_CONTACT_DATE_OUT));
                break;
            case DirectionProviderInterface::DIRECTION_OUTGOING:
                $this->assertEquals(1, $accessor->getValue($target, ActivityScope::CONTACT_COUNT));
                $this->assertEquals(1, $accessor->getValue($target, ActivityScope::CONTACT_COUNT_OUT));
                $this->assertNull($accessor->getValue($target, ActivityScope::CONTACT_COUNT_IN));
                $this->assertInstanceOf(
                    \DateTime::class,
                    $accessor->getValue($target, ActivityScope::LAST_CONTACT_DATE)
                );
                $this->assertInstanceOf(
                    \DateTime::class,
                    $accessor->getValue($target, ActivityScope::LAST_CONTACT_DATE_OUT)
                );
                $this->assertNull($accessor->getValue($target, ActivityScope::LAST_CONTACT_DATE_IN));
                break;
            default:
                $this->assertNull($accessor->getValue($target, ActivityScope::CONTACT_COUNT));
                $this->assertNull($accessor->getValue($target, ActivityScope::CONTACT_COUNT_OUT));
                $this->assertNull($accessor->getValue($target, ActivityScope::CONTACT_COUNT_IN));
                $this->assertNull($accessor->getValue($target, ActivityScope::LAST_CONTACT_DATE));
                $this->assertNull($accessor->getValue($target, ActivityScope::LAST_CONTACT_DATE_OUT));
                $this->assertNull($accessor->getValue($target, ActivityScope::LAST_CONTACT_DATE));
        }
    }

    public function testOnAddActivityWithNonExtendedEntity()
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $target = new TestTarget();
        $object = new TestActivity(DirectionProviderInterface::DIRECTION_INCOMING, new \DateTime());
        $event = new ActivityEvent($object, $target);

        $this->config->expects($this->once())
            ->method('is')
            ->with('is_extend')
            ->willReturn(false);

        $this->listener->onAddActivity($event);
        $this->assertNull($accessor->getValue($target, ActivityScope::CONTACT_COUNT));
    }

    public function onAddActivityProvider(): array
    {
        return [
            'incoming'    => [
                new TestActivity(DirectionProviderInterface::DIRECTION_INCOMING, new \DateTime()),
                DirectionProviderInterface::DIRECTION_INCOMING
            ],
            'outgoing'    => [
                new TestActivity(DirectionProviderInterface::DIRECTION_OUTGOING, new \DateTime()),
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
            (new Account())->setId(2)->setCreatedAt(new \DateTime()),
            (new Account())->setId(3)->setCreatedAt(new \DateTime()),
            (new Account())->setId(4)->setCreatedAt(new \DateTime())
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

        $activity = new Email();
        ReflectionUtil::setId($activity, 1);
        $activity->setActivityTargets($targets);
        $activity->setCreated(new \DateTime());
        $em = $this->createMock(EntityManager::class);
        $uow = $this->createMock(UnitOfWork::class);

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
        $this->activityContactProvider->expects($this->any())
            ->method('isSupportedEntity')
            ->willReturn(true);

        $this->listener->onFlush(new OnFlushEventArgs($em));
        $this->listener->postFlush(new PostFlushEventArgs($em2));
    }
}
