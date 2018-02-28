<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Oro\Bundle\MagentoBundle\Provider\Connector\DictionaryConnectorInterface;

abstract class AbstractInitialProcessor extends MagentoSyncProcessor
{
    const INITIAL_SYNC_START_DATE = 'initialSyncStartDate';
    const CONNECTORS_INITIAL_SYNCED_TO = 'connectorsInitialSyncedTo';

    /**
     * @param object $entity
     */
    protected function saveEntity($entity)
    {
        /** @var EntityManager $em */
        $em = $this->doctrineRegistry->getManager();
        $em->persist($entity);
        $em->flush($entity);
    }

    /**
     * @param Integration $integration
     */
    protected function processDictionaryConnectors(Integration $integration)
    {
        /** @var ConnectorInterface[] $dictionaryConnectors */
        $dictionaryConnectors = $this->registry->getRegisteredConnectorsTypes(
            $integration->getType(),
            function (ConnectorInterface $connector) {
                return $connector instanceof DictionaryConnectorInterface;
            }
        )->toArray();

        foreach ($dictionaryConnectors as $connector) {
            $this->processIntegrationConnector($integration, $connector);
        }
    }
}
