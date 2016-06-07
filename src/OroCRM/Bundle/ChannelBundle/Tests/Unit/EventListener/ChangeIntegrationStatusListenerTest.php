<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use OroCRM\Bundle\ChannelBundle\Event\ChannelChangeStatusEvent;
use OroCRM\Bundle\ChannelBundle\EventListener\ChangeIntegrationStatusListener;
use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChangeIntegrationStatusListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|RegistryInterface */
    protected $registry;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ChannelChangeStatusEvent */
    protected $event;

    /** @var Channel */
    protected $entity;

    /** @var Integration */
    protected $integration;

    /** @var \PHPUnit_Framework_MockObject_MockObject|EntityManager */
    protected $em;

    protected function setUp()
    {
        $this->registry        = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $this->event           = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Event\ChannelChangeStatusEvent')
            ->disableOriginalConstructor()->getMock();
        $this->em          = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();
        $this->entity          = new Channel();
        $this->integration     = new Integration();

        $this->entity->setDataSource($this->integration);
    }

    protected function tearDown()
    {
        unset($this->entity, $this->integration);
    }

    public function testActivateIntegrationWhenChannelActivatedAndPreviousEnableNotDefined()
    {
        $this->prepareEvent();

        $this->entity->setStatus(true);

        $this->integration->setEnabled(false);
        $this->integration->setPreviouslyEnabled(null);
        $this->integration->setEditMode(Integration::EDIT_MODE_DISALLOW);

        $channelSaveSucceedListener = $this->getListener();
        $channelSaveSucceedListener->onChannelStatusChange($this->event);

        $this->assertTrue($this->integration->isEnabled());
        $this->assertEquals(Integration::EDIT_MODE_RESTRICTED, $this->integration->getEditMode());
    }

    public function testSetPreviousEnableIntegrationWhenChannelActivatedAndPreviousEnableDefined()
    {
        $this->prepareEvent();

        $this->entity->setStatus(true);

        $this->integration->setEnabled(false);
        $this->integration->setPreviouslyEnabled(false);
        $this->integration->setEditMode(Integration::EDIT_MODE_DISALLOW);

        $channelSaveSucceedListener = $this->getListener();
        $channelSaveSucceedListener->onChannelStatusChange($this->event);

        $this->assertFalse($this->integration->isEnabled());
        $this->assertFalse($this->integration->getPreviouslyEnabled());
        $this->assertEquals(Integration::EDIT_MODE_RESTRICTED, $this->integration->getEditMode());
    }

    public function testDeactivateIntegrationWhenChannelDeactivated()
    {
        $this->prepareEvent();

        $this->entity->setStatus(false);

        $this->integration->setEnabled(true);
        $this->integration->setEditMode(Integration::EDIT_MODE_ALLOW);

        $channelSaveSucceedListener = $this->getListener();
        $channelSaveSucceedListener->onChannelStatusChange($this->event);

        $this->assertFalse($this->integration->isEnabled());
        $this->assertEquals(Integration::EDIT_MODE_DISALLOW, $this->integration->getEditMode());
    }

    public function testUpdatePreviouslyEnabledWhenChannelDeactivated()
    {
        $this->prepareEvent();

        $this->entity->setStatus(false);

        $this->integration->setEnabled(true);
        $this->integration->setPreviouslyEnabled(null);
        $this->integration->setEditMode(Integration::EDIT_MODE_ALLOW);

        $channelSaveSucceedListener = $this->getListener();
        $channelSaveSucceedListener->onChannelStatusChange($this->event);

        $this->assertTrue($this->integration->getPreviouslyEnabled());
    }

    public function testShouldNotUpdateEditModeIfIntegrationHasDiffEditMode()
    {
        $this->prepareEvent();

        $this->entity->setStatus(false);

        $this->integration->setEnabled(true);
        $this->integration->setPreviouslyEnabled(null);
        $this->integration->setEditMode(0);

        $channelSaveSucceedListener = $this->getListener();
        $channelSaveSucceedListener->onChannelStatusChange($this->event);

        $this->assertSame(0, $this->integration->getEditMode());
    }

    protected function prepareEvent()
    {
        $this->event->expects($this->atLeastOnce())
            ->method('getChannel')
            ->will($this->returnValue($this->entity));

        $this->registry->expects($this->any())->method('getManager')->will($this->returnValue($this->em));
        $this->em->expects($this->once())->method('persist')->with($this->integration);
        $this->em->expects($this->once())->method('flush');
    }

    /**
     * @return ChangeIntegrationStatusListener
     */
    protected function getListener()
    {
        return new ChangeIntegrationStatusListener($this->registry);
    }

    public function assertConnectors()
    {
        $this->assertEquals(
            $this->integration->getConnectors(),
            ['TestConnector1', 'TestConnector2']
        );
    }
}
