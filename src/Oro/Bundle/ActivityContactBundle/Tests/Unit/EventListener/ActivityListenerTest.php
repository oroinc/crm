<?php

namespace Oro\Bundle\ActivityContactBundle\Tests\Unit\EventListener;

use Oro\Bundle\ActivityBundle\Event\ActivityEvent;
use Oro\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use Oro\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use Oro\Bundle\ActivityContactBundle\EventListener\ActivityListener;
use Oro\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;
use Oro\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestActivity;
use Oro\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestDirectionProvider;
use Oro\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestTarget;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Component\Testing\Unit\TestContainerBuilder;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ActivityListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ActivityListener */
    protected $listener;

    /** @var ActivityContactProvider */
    protected $provider;

    /** @var TestTarget */
    protected $testTarget;

    /** @var \DateTime */
    protected $testDate;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $doctrineHelper;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $configManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $configProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $config;

    protected function setUp(): void
    {
        $providers = TestContainerBuilder::create()
            ->add(TestActivity::class, new TestDirectionProvider())
            ->getContainer($this);

        $this->provider = new ActivityContactProvider(
            [TestActivity::class],
            $providers
        );

        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->configProvider = $this->createMock(ConfigProvider::class);
        $this->config = $this->createMock(ConfigInterface::class);

        $this->configManager->expects($this->any())
            ->method('getProvider')
            ->will($this->returnValue($this->configProvider));
        $this->configProvider->expects($this->any())
            ->method('getConfig')
            ->willReturn($this->config);

        $this->listener = new ActivityListener($this->provider, $this->doctrineHelper, $this->configManager);
    }

    /**
     * @dataProvider onRemoveActivityDataProvider
     */
    public function testOnRemoveActivity(string $direction, bool $extend, ?int $expected): void
    {
        $target = new TestTarget();
        $activity = new TestActivity($direction, new \DateTime());

        $this->config
            ->expects($this->any())
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
     * @param object $object
     * @param string $expectedDirection
     */
    public function testOnAddActivity($object, $expectedDirection)
    {
        $this->testTarget = new TestTarget();
        $event            = new ActivityEvent($object, $this->testTarget);

        $this->config->expects($this->once())
            ->method('is')
            ->with('is_extend')
            ->will($this->returnValue(true));

        $this->listener->onAddActivity($event);

        $accessor = PropertyAccess::createPropertyAccessor();
        switch ($expectedDirection) {
            case DirectionProviderInterface::DIRECTION_INCOMING:
                $this->assertEquals(1, $accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT));
                $this->assertEquals(1, $accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT_IN));
                $this->assertNull($accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT_OUT));
                $this->assertInstanceOf(
                    '\DateTime',
                    $accessor->getValue($this->testTarget, ActivityScope::LAST_CONTACT_DATE)
                );
                $this->assertInstanceOf(
                    '\DateTime',
                    $accessor->getValue($this->testTarget, ActivityScope::LAST_CONTACT_DATE_IN)
                );
                $this->assertNull($accessor->getValue($this->testTarget, ActivityScope::LAST_CONTACT_DATE_OUT));
                break;
            case DirectionProviderInterface::DIRECTION_OUTGOING:
                $this->assertEquals(1, $accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT));
                $this->assertEquals(1, $accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT_OUT));
                $this->assertNull($accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT_IN));
                $this->assertInstanceOf(
                    '\DateTime',
                    $accessor->getValue($this->testTarget, ActivityScope::LAST_CONTACT_DATE)
                );
                $this->assertInstanceOf(
                    '\DateTime',
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
            ->will($this->returnValue(false));

        $this->listener->onAddActivity($event);
        $this->assertNull($accessor->getValue($this->testTarget, ActivityScope::CONTACT_COUNT));
    }

    public function onAddActivityProvider()
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
}
