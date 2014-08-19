<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use Symfony\Bridge\Doctrine\RegistryInterface;

use OroCRM\Bundle\ChannelBundle\Event\AbstractEvent;
use OroCRM\Bundle\ChannelBundle\EventListener\ChangeChannelStatusListener;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Event\ChannelChangeStatusEvent;

class ChangeChannelStatusListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|RegistryInterface */
    protected $registry;

    /** @var \PHPUnit_Framework_MockObject_MockObject|EntityManager */
    protected $em;

    /** @var Channel */
    protected $entity;

    /** @var ChannelChangeStatusEvent */
    protected $event;

    /** @var AbstractEvent */
    protected $eventAbstract;

    /** @var Integration */
    protected $integration;

    protected function setUp()
    {
        $this->registry    = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $this->em          = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();
        $this->event       = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Event\ChannelChangeStatusEvent')
            ->disableOriginalConstructor()->getMock();

        $this->eventAbstract = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Event\AbstractEvent')
            ->disableOriginalConstructor()->getMock();

        $this->entity      = new Channel();
        $this->integration = new Integration();
        $this->entity->setDataSource($this->integration);
    }

    protected function tearDown()
    {
        unset($this->entity, $this->integration);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOnChannelStatusChange($status, $isEnabled)
    {
        $this->entity->setStatus($status);

        $this->registry->expects($this->any())->method('getManager')->will($this->returnValue($this->em));
        $this->em->expects($this->exactly(2))->method('persist')->with($this->integration);
        $this->em->expects($this->exactly(2))->method('flush');

        $this->event->expects($this->exactly(1))
            ->method('getChannel')
            ->will($this->returnValue($this->entity));

        $this->eventAbstract->expects($this->exactly(1))
            ->method('getChannel')
            ->will($this->returnValue($this->entity));

        $listener = new ChangeChannelStatusListener($this->registry);
        $listener->onChannelStatusChange($this->event);
        $this->assertEquals($this->integration->getEnabled(), $isEnabled);

        $listener->onChannelStatusChange($this->eventAbstract);
        $this->assertEquals($this->integration->getEnabled(), $isEnabled);
    }

    public function dataProvider()
    {
        return [
            'Active'   => [
                Channel::STATUS_ACTIVE,
                true
            ],
            'Inactive' => [
                Channel::STATUS_INACTIVE,
                false
            ]
        ];

    }
}
