<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use Oro\Bundle\AnalyticsBundle\EventListener\TimezoneChangeListener;
use Oro\Bundle\AnalyticsBundle\Model\RFMMetricStateManager;
use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;

class TimezoneChangeListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RFMMetricStateManager
     */
    protected $manager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CalculateAnalyticsScheduler
     */
    protected $scheduler;

    /**
     * @var TimezoneChangeListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->manager = $this->getMockBuilder('Oro\Bundle\AnalyticsBundle\Model\RFMMetricStateManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scheduler = $this->createCalculateAnalyticsSchedulerMock();

        $this->listener = new TimezoneChangeListener($this->manager, $this->scheduler);
    }

    protected function tearDown()
    {
        unset($this->listener, $this->registry);
    }

    public function testWasNotChanged()
    {
        $this->manager->expects($this->never())
            ->method('resetMetrics');

        $this->scheduler->expects($this->never())
            ->method('scheduleForAllChannels');

        /** @var \PHPUnit_Framework_MockObject_MockObject|ConfigUpdateEvent $event */
        $event = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('isChanged')
            ->with('oro_locale.timezone')
            ->will($this->returnValue(false));

        $this->listener->onConfigUpdate($event);
    }

    public function testSuccessChange()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ConfigUpdateEvent $event */
        $event = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('isChanged')
            ->with('oro_locale.timezone')
            ->will($this->returnValue(true));

        $this->manager->expects($this->once())
            ->method('resetMetrics');

        $this->scheduler->expects($this->once())
            ->method('scheduleForAllChannels');

        $this->manager->expects($this->once())
            ->method('resetMetrics');

        $this->listener->onConfigUpdate($event);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CalculateAnalyticsScheduler
     */
    private function createCalculateAnalyticsSchedulerMock()
    {
        return $this->createMock(CalculateAnalyticsScheduler::class);
    }
}
