<?php

namespace OroCRM\Bundle\ActivityContactBundle\Tests\Unit\EventListener;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\ActivityBundle\Event\ActivityEvent;

use OroCRM\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use OroCRM\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use OroCRM\Bundle\ActivityContactBundle\EventListener\ActivityListener;
use OroCRM\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;
use OroCRM\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestActivity;
use OroCRM\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestDirectionProvider;
use OroCRM\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestTarget;

class ActivityListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ActivityListener */
    protected $listener;

    /** @var ActivityContactProvider */
    protected $provider;

    /** @var TestTarget */
    protected $testTarget;

    /** @var \DateTime */
    protected $testDate;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $doctrineHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $configManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $configProvider;

    public function setUp()
    {
        $this->provider    = new ActivityContactProvider();
        $directionProvider = new TestDirectionProvider();
        $this->provider->addProvider($directionProvider);

        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->setMethods(['getProvider'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->setMethods(['getConfig', 'is'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configManager
            ->method('getProvider')
            ->will($this->returnValue($this->configProvider));

        $this->configProvider
            ->method('getConfig')
            ->will($this->returnValue($this->configProvider));

        $this->listener = new ActivityListener($this->provider, $this->doctrineHelper, $this->configManager);
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

        $this->configProvider
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

        $this->configProvider
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
