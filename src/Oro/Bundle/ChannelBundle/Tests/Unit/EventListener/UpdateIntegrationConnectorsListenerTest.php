<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use Oro\Bundle\ChannelBundle\EventListener\UpdateIntegrationConnectorsListener;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

class UpdateIntegrationConnectorsListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry */
    protected $registry;

    /** @var \PHPUnit\Framework\MockObject\MockObject|SettingsProvider */
    protected $settingProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ChannelSaveEvent */
    protected $event;

    /** @var Channel */
    protected $entity;

    /** @var Integration */
    protected $integration;

    /** @var \PHPUnit\Framework\MockObject\MockObject|EntityManager */
    protected $em;

    protected function setUp(): void
    {
        $this->registry = $this->createMock('Doctrine\Persistence\ManagerRegistry');
        $this->settingProvider = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();
        $this->event           = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Event\ChannelSaveEvent')
            ->setMethods(['getDataSource', 'getChannel'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->em          = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();
        $this->entity          = new Channel();
        $this->integration     = new Integration();

        $this->entity->setDataSource($this->integration);
    }

    protected function tearDown(): void
    {
        unset($this->entity, $this->integration);
    }

    public function testOnChannelSave()
    {
        $this->prepareEvent();

        $channelSaveSucceedListener = $this->getListener();
        $channelSaveSucceedListener->onChannelSave($this->event);

        $this->assertConnectors();
    }

    protected function prepareEvent()
    {
        $this->entity->setEntities(
            [
                'Oro\Bundle\AcmeBundle\Entity\TestEntity1',
                'Oro\Bundle\AcmeBundle\Entity\TestEntity2',
            ]
        );

        $this->event->expects($this->atLeastOnce())
            ->method('getChannel')
            ->will($this->returnValue($this->entity));

        $this->settingProvider
            ->expects($this->at(0))
            ->method('getIntegrationConnectorName')
            ->with('Oro\Bundle\AcmeBundle\Entity\TestEntity1')
            ->will($this->returnValue('TestConnector1'));

        $this->settingProvider
            ->expects($this->at(1))
            ->method('getIntegrationConnectorName')
            ->with('Oro\Bundle\AcmeBundle\Entity\TestEntity2')
            ->will($this->returnValue('TestConnector2'));

        $this->registry->expects($this->any())->method('getManager')->will($this->returnValue($this->em));
        $this->em->expects($this->once())->method('persist')->with($this->integration);
        $this->em->expects($this->once())->method('flush');
    }

    /**
     * @return UpdateIntegrationConnectorsListener
     */
    protected function getListener()
    {
        return new UpdateIntegrationConnectorsListener($this->settingProvider, $this->registry);
    }

    public function assertConnectors()
    {
        $this->assertEquals(
            $this->integration->getConnectors(),
            ['TestConnector1', 'TestConnector2']
        );
    }
}
