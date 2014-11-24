<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Event\ChannelChangeStatusEvent;
use OroCRM\Bundle\ChannelBundle\EventListener\ChangeChannelStatusListener;

class ChangeChannelStatusListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|RegistryInterface */
    protected $registry;

    /** @var \PHPUnit_Framework_MockObject_MockObject|EntityManager */
    protected $em;

    /** @var ChannelChangeStatusEvent */
    protected $event;

    protected function setUp()
    {
        $this->registry = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $this->em       = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();
    }

    protected function tearDown()
    {
        unset($this->registry, $this->em);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOnChannelStatusChange($status, $isEnabled)
    {
        $integration = new Integration();
        $entity      = new Channel();
        $entity->setStatus($status);
        $entity->setDataSource($integration);

        $this->registry->expects($this->any())->method('getManager')->will($this->returnValue($this->em));
        $this->em->expects($this->once())->method('persist')->with($integration);
        $this->em->expects($this->once())->method('flush');

        $listener = new ChangeChannelStatusListener($this->registry);
        $listener->onChannelStatusChange(new ChannelChangeStatusEvent($entity));
        $this->assertEquals($integration->isEnabled(), $isEnabled);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'Active' => [
                Channel::STATUS_ACTIVE,
                true
            ],
            'Inactive' => [
                Channel::STATUS_INACTIVE,
                false
            ]
        ];
    }

    public function testOnChannelStatusChangeOnAbstractEvent()
    {
        $eventAbstract = $this->getMockForAbstractClass(
            'OroCRM\Bundle\ChannelBundle\Event\AbstractEvent',
            [new Channel()]
        );

        $listener = new ChangeChannelStatusListener($this->registry);
        $listener->onChannelStatusChange($eventAbstract);
    }
}
