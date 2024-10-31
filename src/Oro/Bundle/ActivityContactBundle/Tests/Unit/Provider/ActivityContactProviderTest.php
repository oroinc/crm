<?php

namespace Oro\Bundle\ActivityContactBundle\Tests\Unit\Provider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use Oro\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;
use Oro\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestActivity;
use Oro\Bundle\ActivityContactBundle\Tests\Unit\Stub\EmailStub as TestActivity1;
use Oro\Component\Testing\Unit\TestContainerBuilder;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ActivityContactProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var DirectionProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $directionProvider;

    /** @var DirectionProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $directionProvider1;

    /** @var ActivityContactProvider */
    private $provider;

    /** @var ActivityManager|\PHPUnit\Framework\MockObject\MockObject */
    private $activityManager;

    protected function setUp(): void
    {
        $this->directionProvider = $this->createMock(DirectionProviderInterface::class);
        $this->directionProvider1 = $this->createMock(DirectionProviderInterface::class);
        $this->activityManager = $this->createMock(ActivityManager::class);

        $providers = TestContainerBuilder::create()
            ->add(TestActivity::class, $this->directionProvider)
            ->add(TestActivity1::class, $this->directionProvider1)
            ->getContainer($this);

        $this->provider = new ActivityContactProvider(
            [TestActivity::class, TestActivity1::class],
            $providers
        );

        $this->provider->setActivityManager($this->activityManager);
    }

    public function testGetActivityDirection(): void
    {
        $this->directionProvider->expects(self::once())
            ->method('getDirection')
            ->willReturnCallback(function (TestActivity $activity) {
                return $activity->getDirection();
            });

        $activity = new TestActivity(DirectionProviderInterface::DIRECTION_INCOMING, new \DateTime());
        self::assertEquals(
            DirectionProviderInterface::DIRECTION_INCOMING,
            $this->provider->getActivityDirection($activity, new \stdClass())
        );
    }

    public function testGetActivityDirectionForNotSupportedActivity(): void
    {
        $this->directionProvider->expects(self::never())
            ->method('getDirection');

        self::assertEquals(
            DirectionProviderInterface::DIRECTION_UNKNOWN,
            $this->provider->getActivityDirection(new \stdClass(), new \stdClass())
        );
    }

    public function testGetActivityDate(): void
    {
        $this->directionProvider->expects(self::once())
            ->method('getDate')
            ->willReturnCallback(function (TestActivity $activity) {
                return $activity->getCreated();
            });

        $date = new \DateTime('2015-01-01');
        $activity = new TestActivity(DirectionProviderInterface::DIRECTION_INCOMING, $date);
        self::assertSame($date, $this->provider->getActivityDate($activity));
    }

    public function testGetActivityDateForNotSupportedActivity(): void
    {
        $this->directionProvider->expects(self::never())
            ->method('getDate');

        self::assertNull($this->provider->getActivityDate(new \stdClass()));
    }

    public function testGetSupportedActivityClasses(): void
    {
        self::assertEquals(
            [TestActivity::class, TestActivity1::class],
            $this->provider->getSupportedActivityClasses()
        );
    }

    public function testIsSupportedEntity(): void
    {
        self::assertTrue($this->provider->isSupportedEntity(TestActivity::class));
    }

    public function testIsSupportedEntityForNotSupportedActivity(): void
    {
        self::assertFalse($this->provider->isSupportedEntity(\stdClass::class));
    }

    public function testGetLastContactActivityDate(): void
    {
        $em = $this->createMock(EntityManager::class);
        $targetEntity = new \stdClass();
        $direction = DirectionProviderInterface::DIRECTION_INCOMING;
        $allDate1 = new \DateTime('2015-01-01');
        $directionDate1 = new \DateTime('2015-01-01');
        $allDate2 = new \DateTime('2015-01-02');
        $directionDate2 = new \DateTime('2015-01-02');

        $this->activityManager->expects(self::exactly(2))
            ->method('hasActivityAssociation')
            ->withConsecutive(
                ['stdClass', TestActivity::class],
                ['stdClass', TestActivity1::class]
            )
            ->willReturn(true, true);

        $this->directionProvider->expects(self::once())
            ->method('getLastActivitiesDateForTarget')
            ->with(self::identicalTo($em), self::identicalTo($targetEntity), $direction, self::isNull())
            ->willReturn(['all' => $allDate1, 'direction' => $directionDate1]);
        $this->directionProvider1->expects(self::once())
            ->method('getLastActivitiesDateForTarget')
            ->with(self::identicalTo($em), self::identicalTo($targetEntity), $direction, self::isNull())
            ->willReturn(['all' => $allDate2, 'direction' => $directionDate2]);

        self::assertSame(
            ['all' => $allDate2, 'direction' => $directionDate2],
            $this->provider->getLastContactActivityDate($em, $targetEntity, $direction)
        );
    }

    public function testGetLastContactActivityDateWithOneRelation(): void
    {
        $em = $this->createMock(EntityManager::class);
        $targetEntity = new \stdClass();
        $direction = DirectionProviderInterface::DIRECTION_INCOMING;
        $allDate1 = new \DateTime('2015-01-01');
        $directionDate1 = new \DateTime('2015-01-01');

        $this->activityManager->expects(self::exactly(2))
            ->method('hasActivityAssociation')
            ->withConsecutive(
                ['stdClass', TestActivity::class],
                ['stdClass', TestActivity1::class]
            )
            ->willReturn(true, false);

        $this->directionProvider->expects(self::once())
            ->method('getLastActivitiesDateForTarget')
            ->with(self::identicalTo($em), self::identicalTo($targetEntity), $direction, self::isNull())
            ->willReturn(['all' => $allDate1, 'direction' => $directionDate1]);
        $this->directionProvider1->expects(self::never())
            ->method('getLastActivitiesDateForTarget');

        self::assertSame(
            ['all' => $allDate1, 'direction' => $directionDate1],
            $this->provider->getLastContactActivityDate($em, $targetEntity, $direction)
        );
    }

    public function testGetLastContactActivityDateWithoutRelations(): void
    {
        $em = $this->createMock(EntityManager::class);
        $targetEntity = new \stdClass();
        $direction = DirectionProviderInterface::DIRECTION_INCOMING;

        $this->activityManager->expects(self::exactly(2))
            ->method('hasActivityAssociation')
            ->withConsecutive(
                ['stdClass', TestActivity::class],
                ['stdClass', TestActivity1::class]
            )
            ->willReturn(false, false);

        $this->directionProvider->expects(self::never())
            ->method('getLastActivitiesDateForTarget');
        $this->directionProvider1->expects(self::never())
            ->method('getLastActivitiesDateForTarget');

        self::assertSame(
            ['all' => null, 'direction' => null],
            $this->provider->getLastContactActivityDate($em, $targetEntity, $direction)
        );
    }

    public function testGetLastContactActivityDateWithImmutableDates(): void
    {
        $em = $this->createMock(EntityManager::class);
        $targetEntity = new \stdClass();
        $direction = DirectionProviderInterface::DIRECTION_INCOMING;
        $allDate1 = new \DateTimeImmutable('2015-01-01');
        $directionDate1 = new \DateTimeImmutable('2015-01-01');
        $allDate2 = new \DateTimeImmutable('2015-01-02');
        $directionDate2 = new \DateTimeImmutable('2015-01-02');

        $this->activityManager->expects(self::exactly(2))
            ->method('hasActivityAssociation')
            ->withConsecutive(
                ['stdClass', TestActivity::class],
                ['stdClass', TestActivity1::class]
            )
            ->willReturn(true, true);

        $this->directionProvider->expects(self::once())
            ->method('getLastActivitiesDateForTarget')
            ->with(self::identicalTo($em), self::identicalTo($targetEntity), $direction, self::isNull())
            ->willReturn(['all' => $allDate1, 'direction' => $directionDate1]);
        $this->directionProvider1->expects(self::once())
            ->method('getLastActivitiesDateForTarget')
            ->with(self::identicalTo($em), self::identicalTo($targetEntity), $direction, self::isNull())
            ->willReturn(['all' => $allDate2, 'direction' => $directionDate2]);

        self::assertSame(
            ['all' => $allDate2, 'direction' => $directionDate2],
            $this->provider->getLastContactActivityDate($em, $targetEntity, $direction)
        );
    }

    public function testGetLastContactActivityDateWhenNoDates(): void
    {
        $em = $this->createMock(EntityManager::class);
        $targetEntity = new \stdClass();
        $direction = DirectionProviderInterface::DIRECTION_INCOMING;

        $this->activityManager->expects(self::exactly(2))
            ->method('hasActivityAssociation')
            ->withConsecutive(
                ['stdClass', TestActivity::class],
                ['stdClass', TestActivity1::class]
            )
            ->willReturn(true, true);

        $this->directionProvider->expects(self::once())
            ->method('getLastActivitiesDateForTarget')
            ->with(self::identicalTo($em), self::identicalTo($targetEntity), $direction, self::isNull())
            ->willReturn([]);

        self::assertSame(
            ['all' => null, 'direction' => null],
            $this->provider->getLastContactActivityDate($em, $targetEntity, $direction)
        );
    }

    public function testGetActivityDirectionProvider(): void
    {
        self::assertSame(
            $this->directionProvider,
            $this->provider->getActivityDirectionProvider(
                new TestActivity(DirectionProviderInterface::DIRECTION_INCOMING, new \DateTime())
            )
        );
    }

    public function testGetActivityDirectionProviderForNotSupportedActivity(): void
    {
        self::assertNull($this->provider->getActivityDirectionProvider(new \stdClass()));
    }
}
