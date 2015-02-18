<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use JMS\JobQueueBundle\Entity\Job;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use OroCRM\Bundle\MagentoBundle\Command\InitialSyncCommand;

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
        $initialSyncedTo = $this->getInitialSyncedTo($integration);

        // Set lastSyncDate to now if initial sync is executed at first time
        if (!$this->isInitialSyncStarted($integration)) {
            $syncSettings = $integration->getSynchronizationSettings();
            $syncSettings->offsetSet(self::INITIAL_SYNC_STARTED, true);
            $syncSettings->offsetSet(self::INITIAL_SYNCED_TO, $initialSyncedTo->format(\DateTime::ISO8601));
            $this->saveEntity($integration);

            $parameters[AbstractMagentoConnector::LAST_SYNC_KEY] = $initialSyncedTo;
        }

        // In case when initial sync does not started yet, it failed or start sync date was changed - run initial sync
        if (!$this->isInitialJobRunning($integration) && $initialSyncedTo > $startSyncDate) {
            $this->scheduleInitialSync($integration);
        }

        // Run incremental sync
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
