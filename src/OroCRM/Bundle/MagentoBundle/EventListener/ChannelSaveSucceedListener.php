<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use OroCRM\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use OroCRM\Bundle\ChannelBundle\EventListener\ChannelSaveSucceedListener as BaseChannelSaveSucceedListener;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;
use OroCRM\Bundle\MagentoBundle\Provider\ExtensionAwareInterface;
use OroCRM\Bundle\MagentoBundle\Provider\InitialSyncProcessor;

/**
 * Add initial connectors to connectors list.
 * Skip connectors that require Oro Bridge extension in case when it does not installed.
 */
class ChannelSaveSucceedListener extends BaseChannelSaveSucceedListener
{
    /**
     * @var TypesRegistry
     */
    protected $typeRegistry;

    /**
     * @var MagentoSoapTransport
     */
    protected $transportEntity;

    /**
     * @param TypesRegistry $registry
     */
    public function setConnectorsTypeRegistry(TypesRegistry $registry)
    {
        $this->typeRegistry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function onChannelSucceedSave(ChannelSaveEvent $event)
    {
        $channel = $event->getChannel();

        if ($channel->getChannelType() === ChannelType::TYPE
            && $channel->getDataSource()->getTransport() instanceof MagentoSoapTransport
        ) {
            $this->transportEntity = $channel->getDataSource()->getTransport();
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
                $connector = $this->typeRegistry->getConnectorType(ChannelType::TYPE, $connectorName);
                $isExtensionInstalled = $this->transportEntity->getIsExtensionInstalled();
                if ($isExtensionInstalled
                    || (!$isExtensionInstalled && !$connector instanceof ExtensionAwareInterface)
                ) {
                    array_push($initialConnectors, $connectorName . InitialSyncProcessor::INITIAL_CONNECTOR_SUFFIX);
                    array_push($connectors, $connectorName);
                }
            }
        }

        return array_merge($initialConnectors, $connectors);
    }
}
