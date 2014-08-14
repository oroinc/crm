<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use Symfony\Bridge\Doctrine\RegistryInterface;

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

    /** @var Integration */
    protected $integration;

    protected function setUp()
    {
        $this->registry    = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $this->em          = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();
        $this->event       = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Event\ChannelChangeStatusEvent')
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

        $this->registry->expects($this->any())->method('getEntityManager')->will($this->returnValue($this->em));
        $this->em->expects($this->once())->method('persist')->with($this->integration);
        $this->em->expects($this->once())->method('flush');

        $this->event->expects($this->once())
            ->method('getChannel')
            ->will($this->returnValue($this->entity));

        $listener = new ChangeChannelStatusListener($this->registry);
        $listener->onChannelStatusChange($this->event);

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
