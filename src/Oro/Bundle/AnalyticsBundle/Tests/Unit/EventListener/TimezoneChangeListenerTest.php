<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\EventListener;

use Oro\Bundle\AnalyticsBundle\EventListener\TimezoneChangeListener;
use Oro\Bundle\AnalyticsBundle\Model\RFMMetricStateManager;
use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;

class TimezoneChangeListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|RFMMetricStateManager
     */
    protected $manager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|CalculateAnalyticsScheduler
     */
    protected $scheduler;

    /**
     * @var TimezoneChangeListener
     */
    protected $listener;

    protected function setUp(): void
    {
        $this->manager = $this->getMockBuilder('Oro\Bundle\AnalyticsBundle\Model\RFMMetricStateManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scheduler = $this->createCalculateAnalyticsSchedulerMock();

        $this->listener = new TimezoneChangeListener($this->manager, $this->scheduler);
    }

    protected function tearDown(): void
    {
        unset($this->listener, $this->registry);
    }

    public function testWasNotChanged()
    {
        $this->manager->expects($this->never())
            ->method('resetMetrics');

        $this->scheduler->expects($this->never())
            ->method('scheduleForAllChannels');

        /** @var \PHPUnit\Framework\MockObject\MockObject|ConfigUpdateEvent $event */
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
        /** @var \PHPUnit\Framework\MockObject\MockObject|ConfigUpdateEvent $event */
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
     * @return \PHPUnit\Framework\MockObject\MockObject|CalculateAnalyticsScheduler
     */
    private function createCalculateAnalyticsSchedulerMock()
    {
        return $this->createMock(CalculateAnalyticsScheduler::class);
    }
}
