<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Oro\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use Oro\Bundle\ChannelBundle\EventListener\UpdateIntegrationConnectorsListener as BaseUpdateConnectorsListener;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Oro\Bundle\MagentoBundle\Provider\Connector\DictionaryConnectorInterface;
use Oro\Bundle\MagentoBundle\Provider\ExtensionAwareInterface;
use Oro\Bundle\MagentoBundle\Provider\ExtensionVersionAwareInterface;
use Oro\Bundle\MagentoBundle\Provider\InitialSyncProcessor;

/**
 * Add initial connectors to connectors list.
 * Skip connectors that require Oro Bridge extension in case when it does not installed.
 */
class UpdateIntegrationConnectorsListener extends BaseUpdateConnectorsListener
{
    /**
     * @var TypesRegistry
     */
    protected $typeRegistry;

    /**
     * @var MagentoTransport
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
    public function onChannelSave(ChannelSaveEvent $event)
    {
        $channel = $event->getChannel();

        if ($channel->getChannelType() === MagentoChannelType::TYPE
            && $channel->getDataSource()->getTransport() instanceof MagentoTransport
        ) {
            $this->transportEntity = $channel->getDataSource()->getTransport();

            parent::onChannelSave($event);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectors(array $entities)
    {
        $dictionaryConnectors = $this->typeRegistry->getRegisteredConnectorsTypes(
            MagentoChannelType::TYPE,
            function (ConnectorInterface $connector) {
                return $connector instanceof DictionaryConnectorInterface;
            }
        )->toArray();
        $connectors = [];
        $initialConnectors = [];
        $isSupportedExtensionVersion = $this->transportEntity->isSupportedExtensionVersion();
        $isExtensionInstalled = $this->transportEntity->getIsExtensionInstalled();

        foreach ($entities as $entity) {
            $connectorName = $this->settingsProvider->getIntegrationConnectorName($entity);
            if ($connectorName) {
                $connector = $this->typeRegistry->getConnectorType(MagentoChannelType::TYPE, $connectorName);
                if (!$connector) {
                    continue;
                }

                $isExtensionApplicable = $connector instanceof ExtensionVersionAwareInterface ?
                    $isSupportedExtensionVersion : $isExtensionInstalled;

                if ($isExtensionApplicable
                    || (!$isExtensionApplicable && !$connector instanceof ExtensionAwareInterface)
                ) {
                    array_push($initialConnectors, $connectorName . InitialSyncProcessor::INITIAL_CONNECTOR_SUFFIX);
                    array_push($connectors, $connectorName);
                }
            }
        }

        return array_merge(array_keys($dictionaryConnectors), $initialConnectors, $connectors);
    }
}
