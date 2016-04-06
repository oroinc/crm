<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\ORM\EntityManager;

use OroCRM\Bundle\ChannelBundle\Event\AbstractEvent;
use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class UpdateIntegrationConnectorsListener
{
    /** @var SettingsProvider */
    protected $settingsProvider;

    /** @var RegistryInterface */
    protected $registry;

    /**
     * @param SettingsProvider  $settingsProvider
     * @param RegistryInterface $registry
     */
    public function __construct(SettingsProvider $settingsProvider, RegistryInterface $registry)
    {
        $this->settingsProvider = $settingsProvider;
        $this->registry         = $registry;
    }

    /**
     * @param AbstractEvent $event
     */
    public function onChannelSave(AbstractEvent $event)
    {
        /** @var Channel $channel */
        $channel    = $event->getChannel();
        $dataSource = $channel->getDataSource();

        if ($dataSource instanceof Integration) {
            $entities   = $channel->getEntities();
            $connectors = $this->getConnectors($entities);

            $dataSource->setConnectors($connectors);

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

            if (!empty($connectorName)) {
                array_push(
                    $result,
                    $connectorName
                );
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
