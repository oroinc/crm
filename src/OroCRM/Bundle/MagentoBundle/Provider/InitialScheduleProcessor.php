<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use JMS\JobQueueBundle\Entity\Job;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use OroCRM\Bundle\MagentoBundle\Command\InitialSyncCommand;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;

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
        $this->scheduleInitialSyncIfRequired($integration);

        /** @var MagentoSoapTransport $transport */
        $transport = $integration->getTransport();
        // Run incremental sync
        $parameters[AbstractMagentoConnector::LAST_SYNC_KEY] = $transport->getInitialSyncStartDate();

        return parent::process($integration, $connector, $parameters);
    }

    /**
     * @param Integration $integration
     * @param \DateTime $initialSyncStartDate
     * @param \DateTime $startSyncDate
     * @return bool
     */
    protected function isInitialSyncRequired(
        Integration $integration,
        \DateTime $initialSyncStartDate,
        \DateTime $startSyncDate
    ) {
        $connectors = $this->getInitialConnectors($integration);
        $syncDates = [];
        foreach ($connectors as $connector) {
            $lastSyncedTo = $this->getLastStatusForConnector($integration, $connector);
            if (!$lastSyncedTo) {
                $lastSyncedTo = $initialSyncStartDate;
            }

            $syncDates[] = $lastSyncedTo;
        }

        if ($syncDates) {
            $minSyncedTo = min($syncDates);
            return $minSyncedTo > $startSyncDate;
        }

        return false;
    }

    /**
     * @param Integration $integration
     * @return array
     */
    protected function getInitialConnectors(Integration $integration)
    {
        $connectors = $integration->getConnectors();

        return array_filter(
            $connectors,
            function ($connector) {
                return strpos($connector, InitialSyncProcessor::INITIAL_CONNECTOR_SUFFIX) !== false;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function processConnectors(Integration $integration, array $parameters = [], callable $callback = null)
    {
        if (null === $callback) {
            $callback = function ($connector) {
                return strpos($connector, InitialSyncProcessor::INITIAL_CONNECTOR_SUFFIX) === false;
            };
        }

        return parent::processConnectors($integration, $parameters, $callback);
    }

    /**
     * @param Integration $integration
     * @return bool
     */
    protected function isInitialJobRunning(Integration $integration)
    {
        /** @var ChannelRepository $repository */
        $repository = $this->doctrineRegistry->getRepository('OroIntegrationBundle:Channel');
        $initialJobsRunning = $repository->getRunningSyncJobsCount(
            InitialSyncCommand::COMMAND_NAME,
            $integration->getId()
        );

        return $initialJobsRunning > 1;
    }

    /**
     * In case when initial sync does not started yet, it failed or start sync date was changed - run initial sync.
     *
     * @param Integration $integration
     */
    protected function scheduleInitialSyncIfRequired(Integration $integration)
    {
        $this->checkInitialSyncStartDate($integration);
        /** @var MagentoSoapTransport $transport */
        $transport = $integration->getTransport();
        if (!$this->isInitialJobRunning($integration)
            && $this->isInitialSyncRequired(
                $integration,
                $transport->getInitialSyncStartDate(),
                $transport->getSyncStartDate()
            )
        ) {
            $job = new Job(InitialSyncCommand::COMMAND_NAME, [sprintf('--integration-id=%s', $integration->getId())]);
            $this->saveEntity($job);
        }
    }

    /**
     * Save initial sync start date and flag initial sync as started.
     *
     * @param Integration $integration
     */
    protected function checkInitialSyncStartDate(Integration $integration)
    {
        if (!$this->isInitialSyncStarted($integration)) {
            /** @var MagentoSoapTransport $transport */
            $transport = $integration->getTransport();
            $initialSyncStartDate = $this->getInitialSyncStartDate($integration);
            $transport->setInitialSyncStartDate($initialSyncStartDate);
            $this->saveEntity($transport);
        }
    }
}
