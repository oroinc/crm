<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use OroCRM\Bundle\ChannelBundle\EventListener\ChannelSaveSucceedListener as BaseChannelSaveSucceedListener;
use OroCRM\Bundle\MagentoBundle\Provider\InitialSyncProcessor;

class ChannelSaveSucceedListener extends BaseChannelSaveSucceedListener
{
    /**
     * @param array $entities
     *
     * @return array
     */
    protected function getConnectors(array $entities)
    {
        $connectors = [];
        $initialConnectors = [];

        foreach ($entities as $entity) {
            $connectorName = $this->settingsProvider->getIntegrationConnectorName($entity);
            if ($connectorName) {
                array_push($initialConnectors, $connectorName . InitialSyncProcessor::INITIAL_CONNECTOR_SUFFIX);
                array_push($connectors, $connectorName);
            }
        }

        return array_merge($initialConnectors, $connectors);
    }
}
