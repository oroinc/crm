<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use Oro\Bundle\ChannelBundle\EventListener\UpdateIntegrationConnectorsListener;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateIntegrationConnectorsListenerTest extends TestCase
{
    private ManagerRegistry&MockObject $registry;
    private SettingsProvider&MockObject $settingProvider;
    private ChannelSaveEvent&MockObject $event;
    private Channel $entity;
    private Integration $integration;
    private EntityManager&MockObject $em;
    private UpdateIntegrationConnectorsListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->settingProvider = $this->createMock(SettingsProvider::class);
        $this->event = $this->createMock(ChannelSaveEvent::class);
        $this->em = $this->createMock(EntityManager::class);
        $this->entity = new Channel();
        $this->integration = new Integration();

        $this->entity->setDataSource($this->integration);

        $this->listener = new UpdateIntegrationConnectorsListener(
            $this->settingProvider,
            $this->registry
        );
    }

    public function testOnChannelSave(): void
    {
        $this->prepareEvent();

        $this->listener->onChannelSave($this->event);

        $this->assertEquals(
            ['TestConnector1', 'TestConnector2'],
            $this->integration->getConnectors()
        );
    }

    private function prepareEvent()
    {
        $this->entity->setEntities(
            [
                'Oro\Bundle\AcmeBundle\Entity\TestEntity1',
                'Oro\Bundle\AcmeBundle\Entity\TestEntity2',
            ]
        );

        $this->event->expects($this->atLeastOnce())
            ->method('getChannel')
            ->willReturn($this->entity);

        $this->settingProvider->expects($this->exactly(2))
            ->method('getIntegrationConnectorName')
            ->willReturnMap([
                ['Oro\Bundle\AcmeBundle\Entity\TestEntity1', 'TestConnector1'],
                ['Oro\Bundle\AcmeBundle\Entity\TestEntity2', 'TestConnector2']
            ]);

        $this->registry->expects($this->any())
            ->method('getManager')
            ->willReturn($this->em);
        $this->em->expects($this->once())
            ->method('persist')
            ->with($this->integration);
        $this->em->expects($this->once())
            ->method('flush');
    }
}
