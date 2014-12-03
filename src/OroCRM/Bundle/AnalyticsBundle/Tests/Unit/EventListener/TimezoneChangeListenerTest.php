<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Unit\EventListener;

use JMS\JobQueueBundle\Entity\Job;

use OroCRM\Bundle\AnalyticsBundle\Command\CalculateAnalyticsCommand;
use OroCRM\Bundle\AnalyticsBundle\EventListener\TimezoneChangeListener;

class TimezoneChangeListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var TimezoneChangeListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->getMock();
        $this->listener = new TimezoneChangeListener($this->registry);
    }

    protected function tearDown()
    {
        unset($this->listener, $this->registry);
    }

    public function testWasNotChanged()
    {
        $this->registry->expects($this->never())
            ->method('getManager');

        $event = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('isChanged')
            ->with('oro_locale.timezone')
            ->will($this->returnValue(false));

        $this->listener->onConfigUpdate($event);
    }

    public function testChangedButAlreadyScheduled()
    {
        $event = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('isChanged')
            ->with('oro_locale.timezone')
            ->will($this->returnValue(true));

        $this->registry->expects($this->never())
            ->method('getManager');
        $this->assertIsChangedCalled(true);

        $this->listener->onConfigUpdate($event);
    }

    public function testSuccessChange()
    {
        $event = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('isChanged')
            ->with('oro_locale.timezone')
            ->will($this->returnValue(true));

        $this->assertIsChangedCalled(false);

        $em = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->getMock();

        $em->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('JMS\JobQueueBundle\Entity\Job'));
        $em->expects($this->once())
            ->method('flush');

        $this->registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($em));

        $this->listener->onConfigUpdate($event);
    }

    protected function assertIsChangedCalled($changed)
    {
        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry->expects($this->once())->method('getRepository')
            ->with('JMSJobQueueBundle:Job')
            ->will($this->returnValue($repo));
        $repo->expects($this->once())
            ->method('findOneBy')
            ->with(['command' => CalculateAnalyticsCommand::COMMAND_NAME, 'state' => Job::STATE_PENDING])
            ->will($this->returnValue($changed));
    }
}
