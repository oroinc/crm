<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Provider\SyncProcessor;

abstract class AbstractInitialProcessor extends SyncProcessor
{
    const INITIAL_SYNCED_TO = 'initialSyncedTo';

    /**
     * @param Integration $integration
     * @return \DateTime
     */
    protected function getInitialSyncedTo(Integration $integration)
    {
        $syncedTo = null;
        $synchronizationSettings = $integration->getSynchronizationSettings();
        if ($synchronizationSettings->offsetExists(self::INITIAL_SYNCED_TO)) {
            $syncedTo = $synchronizationSettings->offsetGet(self::INITIAL_SYNCED_TO);
        }
        if (!$syncedTo) {
            $syncedTo = 'now';
        }

        return new \DateTime($syncedTo, new \DateTimeZone('UTC'));
    }
}
