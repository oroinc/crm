<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use Oro\Bundle\ChannelBundle\EventListener\UpdateIntegrationConnectorsListener as BaseUpdateConnectorsListener;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Provider\Connector\DictionaryConnectorInterface;
use Oro\Bundle\MagentoBundle\Provider\ExtensionAwareInterface;
use Oro\Bundle\MagentoBundle\Provider\ExtensionVersionAwareInterface;
use Oro\Bundle\MagentoBundle\Provider\InitialSyncProcessor;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

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
     * @var string
     */
    protected $channelType;

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

        if (null === $channel->getDataSource()) {
            return;
        }

        if ($channel->getDataSource()->getTransport() instanceof MagentoTransport) {
            $this->transportEntity = $channel->getDataSource()->getTransport();
            $this->channelType = $channel->getChannelType();
            parent::onChannelSave($event);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectors(array $entities)
    {
        $dictionaryConnectors = $this->typeRegistry->getRegisteredConnectorsTypes(
            $this->channelType,
            function (ConnectorInterface $connector) {
                return $connector instanceof DictionaryConnectorInterface;
            }
        )->toArray();

        /** @var MagentoTransportInterface $transport */
        $transport = $this->typeRegistry
            ->getTransportTypeBySettingEntity($this->transportEntity, $this->channelType);

        $transport->init($this->transportEntity);

        $connectors = [];
        $initialConnectors = [];
        $isSupportedExtensionVersion = $transport->isSupportedExtensionVersion();
        $isExtensionInstalled = $transport->isExtensionInstalled();

        foreach ($entities as $entity) {
            $connectorName = $this->settingsProvider->getIntegrationConnectorName($entity);
            if ($connectorName) {
                $connector = $this->typeRegistry->getConnectorType($this->channelType, $connectorName);
                if (!$connector) {
                    continue;
                }

                $isExtensionApplicable = $connector instanceof ExtensionVersionAwareInterface ?
                    $isSupportedExtensionVersion : $isExtensionInstalled;

                if ($isExtensionApplicable
                    || (!$isExtensionApplicable && !$connector instanceof ExtensionAwareInterface)
                ) {
                    $initialConnectors[] = $connectorName . InitialSyncProcessor::INITIAL_CONNECTOR_SUFFIX;
                    $connectors[] = $connectorName;
                }
            }
        }

        return array_merge(array_keys($dictionaryConnectors), $initialConnectors, $connectors);
    }
}
