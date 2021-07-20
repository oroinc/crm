<?php

namespace Oro\Bundle\ChannelBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Utils\EditModeUtils;

/**
 * Handles channel's integration connections.
 */
class UpdateIntegrationConnectorsListener
{
    /** @var SettingsProvider */
    protected $settingsProvider;

    /** @var ManagerRegistry */
    protected $registry;

    public function __construct(SettingsProvider $settingsProvider, ManagerRegistry $registry)
    {
        $this->settingsProvider = $settingsProvider;
        $this->registry         = $registry;
    }

    public function onChannelSave(ChannelSaveEvent $event)
    {
        /** @var Channel $channel */
        $channel    = $event->getChannel();
        $dataSource = $channel->getDataSource();

        if ($dataSource instanceof Integration) {
            $entities   = $channel->getEntities();
            $connectors = $this->getConnectors($entities);
            $dataSource->setConnectors($connectors);

            $editMode = $channel->getStatus() === Channel::STATUS_ACTIVE
                ? Integration::EDIT_MODE_RESTRICTED
                : Integration::EDIT_MODE_DISALLOW;

            EditModeUtils::attemptChangeEditMode($dataSource, $editMode);

            $this->getManager()->persist($dataSource);
            $this->getManager()->flush();
        }
    }

    /**
     * @param array $entities
     *
     * @return array
     */
    protected function getConnectors(array $entities)
    {
        $result = [];

        foreach ($entities as $entity) {
            $connectorName = $this->settingsProvider->getIntegrationConnectorName($entity);
            if ($connectorName) {
                $result[] = $connectorName;
            }
        }

        return $result;
    }

    /**
     * @return EntityManager
     */
    protected function getManager()
    {
        return $this->registry->getManager();
    }
}
