<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;

use OroCRM\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use OroCRM\Bundle\ChannelBundle\EventListener\ChannelSaveSucceedListener as BaseChannelSaveSucceedListener;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;
use OroCRM\Bundle\MagentoBundle\Provider\ExtensionAwareInterface;

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

        if ($channel->getChannelType() !== ChannelType::TYPE) {
            return;
        }

        $transport = $channel->getDataSource()->getTransport();
        if (!$transport instanceof MagentoSoapTransport) {
            return;
        }

        $this->transportEntity = $transport;

        parent::onChannelSucceedSave($event);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectors(array $entities)
    {
        $isExtensionInstalled = $this->transportEntity->getIsExtensionInstalled();

        $connectors = [];

        foreach ($entities as $entity) {
            $connectorName = $this->settingsProvider->getIntegrationConnectorName($entity);
            if ($connectorName) {
                $connector = $this->typeRegistry->getConnectorType(ChannelType::TYPE, $connectorName);
                if (!$connector) {
                    continue;
                }

                if ($isExtensionInstalled
                    || (!$isExtensionInstalled && !$connector instanceof ExtensionAwareInterface)
                ) {
                    $connectors[] = $connectorName;
                }
            }
        }

        return $connectors;
    }
}
