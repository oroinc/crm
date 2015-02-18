<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Provider\SyncProcessor;

abstract class AbstractInitialProcessor extends SyncProcessor
{
    const INITIAL_SYNC_START_DATE = 'initialSyncStartDate';
    const INITIAL_SYNCED_TO = 'initialSyncedTo';

    /**
     * @param Integration $integration
     * @return \DateTime
     */
    protected function getInitialSyncStartDate(Integration $integration)
    {
        $syncStartDate = null;
        $synchronizationSettings = $integration->getSynchronizationSettings();
        if ($synchronizationSettings->offsetExists(self::INITIAL_SYNC_START_DATE)) {
            $syncStartDate = $synchronizationSettings->offsetGet(self::INITIAL_SYNC_START_DATE);
        }
        if (!$syncStartDate) {
            $syncStartDate = 'now';
        }

        return new \DateTime($syncStartDate, new \DateTimeZone('UTC'));
    }

    /**
     * @param object $entity
     */
    protected function saveEntity($entity)
    {
        $em = $this->doctrineRegistry->getManager();
        $em->persist($entity);
        $em->flush($entity);
    }
}
