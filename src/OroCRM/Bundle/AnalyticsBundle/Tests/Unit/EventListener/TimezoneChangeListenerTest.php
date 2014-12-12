<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use OroCRM\Bundle\AnalyticsBundle\EventListener\TimezoneChangeListener;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMMetricStateManager;

class TimezoneChangeListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RFMMetricStateManager
     */
    protected $manager;

    /**
     * @var TimezoneChangeListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->manager = $this->getMockBuilder('OroCRM\Bundle\AnalyticsBundle\Model\RFMMetricStateManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new TimezoneChangeListener($this->manager);
    }

    protected function tearDown()
    {
        unset($this->listener, $this->registry);
    }

    public function testWasNotChanged()
    {
        $this->manager->expects($this->never())
            ->method('resetMetrics');

        $this->manager->expects($this->never())
            ->method('scheduleRecalculation');

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

        $this->manager->expects($this->once())
            ->method('scheduleRecalculation');

        $this->manager->expects($this->once())
            ->method('resetMetrics');

        $this->manager->expects($this->once())
            ->method('scheduleRecalculation');

        $this->listener->onConfigUpdate($event);
    }
}
