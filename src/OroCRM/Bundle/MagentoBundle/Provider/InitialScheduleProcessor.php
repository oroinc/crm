<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use JMS\JobQueueBundle\Entity\Job;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use OroCRM\Bundle\MagentoBundle\Command\InitialSyncCommand;

/**
 * Schedule initial synchronization if it is required.
 * Limit incremental sync to initial sync start date.
 * Execute incremental sync.
 */
class InitialScheduleProcessor extends AbstractInitialProcessor
{
    const INITIAL_SYNC_STARTED = 'initialSyncedStarted';

    /**
     * {@inheritdoc}
     */
    public function process(Integration $integration, $connector = null, array $parameters = [])
    {
        /** @var \DateTime $startSyncDate */
        $startSyncDate = $integration->getTransport()->getSettingsBag()->get('start_sync_date');
        $syncSettings = $integration->getSynchronizationSettings();
        $initialSyncStartDate = $this->getInitialSyncStartDate($integration);

        // Save initial sync start date and flag initial sync as started
        if (!$this->isInitialSyncStarted($integration)) {
            $syncSettings->offsetSet(self::INITIAL_SYNC_STARTED, true);
            $syncSettings->offsetSet(self::INITIAL_SYNC_START_DATE, $initialSyncStartDate->format(\DateTime::ISO8601));
            $this->saveEntity($integration);
        }

        // Get latest initial synced to date
        $initialSyncedTo = null;
        if ($syncSettings->offsetExists(self::INITIAL_SYNCED_TO)) {
            $initialSyncedTo = $syncSettings->offsetGet(self::INITIAL_SYNCED_TO);
        }
        if (!$initialSyncedTo) {
            $initialSyncedTo = $initialSyncStartDate;
        }

        // In case when initial sync does not started yet, it failed or start sync date was changed - run initial sync
        if (!$this->isInitialJobRunning($integration) && $initialSyncedTo > $startSyncDate) {
            $this->scheduleInitialSync($integration);
        }

        // Run incremental sync
        $parameters[AbstractMagentoConnector::LAST_SYNC_KEY] = $initialSyncStartDate;
        return parent::process($integration, $connector, $parameters);
    }

    /**
     * @param Integration $integration
     * @return bool
     */
    protected function isInitialSyncStarted(Integration $integration)
    {
        $synchronizationSettings = $integration->getSynchronizationSettings();
        if ($synchronizationSettings->offsetExists(self::INITIAL_SYNC_STARTED)) {
            return (bool)$synchronizationSettings->offsetGet(self::INITIAL_SYNC_STARTED);
        }

        return false;
    }

    /**
     * @param Integration $integration
     * @return bool
     */
    protected function isInitialJobRunning(Integration $integration)
    {
        $initialJobsRunning = $this->doctrineRegistry->getRepository('OroIntegrationBundle:Channel')
            ->getRunningSyncJobsCount(InitialSyncCommand::COMMAND_NAME, $integration->getId());

        return $initialJobsRunning > 1;
    }

    /**
     * @param Integration $integration
     */
    protected function scheduleInitialSync(Integration $integration)
    {
        $job = new Job(InitialSyncCommand::COMMAND_NAME, [sprintf('--integration-id=%s', $integration->getId())]);
        $this->saveEntity($job);
    }
}
