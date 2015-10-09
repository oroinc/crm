<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

use JMS\JobQueueBundle\Entity\Job;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;

use OroCRM\Bundle\ChannelBundle\Command\LifetimeAverageAggregateCommand;
use OroCRM\Bundle\ChannelBundle\EventListener\TimezoneChangeListener;

class TimezoneChangeListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registry;

    /** @var TimezoneChangeListener */
    protected $listener;

    /** @var EntityManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject */
    protected $repo;

    protected function setUp()
    {
        $this->registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->repo     = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()->getMock();
        $this->em       = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();
        $this->listener = new TimezoneChangeListener($this->registry);
    }

    protected function tearDown()
    {
        unset($this->listener, $this->em, $this->registry, $this->registry);
    }

    public function testWasNotChanged()
    {
        $this->registry->expects($this->never())->method('getManager');

        $this->listener->onConfigUpdate(new ConfigUpdateEvent([]));
    }

    public function testChangedButAlreadyScheduled()
    {
        $this->registry->expects($this->never())->method('getManager');
        $this->registry->expects($this->once())->method('getRepository')
            ->with('JMSJobQueueBundle:Job')
            ->will($this->returnValue($this->repo));

        $this->repo->expects($this->once())->method('findOneBy')
            ->with(['command' => LifetimeAverageAggregateCommand::COMMAND_NAME, 'state' => Job::STATE_PENDING])
            ->will($this->returnValue($scheduled = true));

        $this->listener->onConfigUpdate(new ConfigUpdateEvent(['oro_locale.timezone' => ['old' => 1, 'new' => 2]]));
    }

    public function testSuccessChange()
    {
        $this->registry->expects($this->once())->method('getManager')
            ->will($this->returnValue($this->em));
        $this->registry->expects($this->once())->method('getRepository')
            ->with('JMSJobQueueBundle:Job')
            ->will($this->returnValue($this->repo));

        $this->repo->expects($this->once())->method('findOneBy')
            ->with(['command' => LifetimeAverageAggregateCommand::COMMAND_NAME, 'state' => Job::STATE_PENDING])
            ->will($this->returnValue($scheduled = false));

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $this->listener->onConfigUpdate(new ConfigUpdateEvent(['oro_locale.timezone' => ['old' => 1, 'new' => 2]]));
    }
}
