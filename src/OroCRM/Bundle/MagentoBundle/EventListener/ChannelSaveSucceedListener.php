<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use OroCRM\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use OroCRM\Bundle\ChannelBundle\EventListener\ChannelSaveSucceedListener as BaseChannelSaveSucceedListener;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;
use OroCRM\Bundle\MagentoBundle\Provider\InitialSyncProcessor;

class ChannelSaveSucceedListener extends BaseChannelSaveSucceedListener
{
    /**
     * {@inheritdoc}
     */
    public function onChannelSucceedSave(ChannelSaveEvent $event)
    {
        if ($event->getChannel()->getChannelType() === ChannelType::TYPE) {
            parent::onChannelSucceedSave($event);
        }
    }

    /**
     * {@inheritdoc}
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
