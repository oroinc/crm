<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityManager;

use JMS\JobQueueBundle\Entity\Job;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\IntegrationBundle\Provider\ForceConnectorInterface;

use OroCRM\Bundle\MagentoBundle\Command\InitialSyncCommand;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\Provider\Connector\DictionaryConnectorInterface;

/**
 * Schedule initial synchronization if it is required.
 * Limit incremental sync to initial sync start date.
 * Execute incremental sync.
 */
class InitialScheduleProcessor extends AbstractInitialProcessor
{
    const INITIAL_SYNC_STARTED = 'initialSyncedStarted';

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     *
     * @return AbstractInitialProcessor
     */
    public function setDoctrineHelper($doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Integration $integration, $connector = null, array $parameters = [])
    {
        if (!empty($parameters['force'])) {
            $this->forceSync($integration);
        }

        $this->processDictionaryConnectors($integration);
        $integration = $this->reloadEntity($integration);
        $this->scheduleInitialSyncIfRequired($integration);

        /** @var MagentoSoapTransport $transport */
        $transport = $integration->getTransport();
        // Run incremental sync
        $parameters[AbstractMagentoConnector::LAST_SYNC_KEY] = $transport->getInitialSyncStartDate();

        return parent::process($integration, $connector, $parameters);
    }

    /**
     * @param Integration $integration
     * @param \DateTime   $initialSyncStartDate
     * @param \DateTime   $startSyncDate
     *
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
            $lastSyncedTo = $this->getSyncedTo($integration, $connector);
            if (!$lastSyncedTo) {
                $lastSyncedTo = $initialSyncStartDate;
            }

            $syncDates[] = $lastSyncedTo;
        }

        if ($syncDates) {
            $maxSyncedTo = max($syncDates);
            return $maxSyncedTo > $startSyncDate;
        }

        return false;
    }

    /**
     * @param Integration $integration
     *
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
                return strpos($connector, InitialSyncProcessor::INITIAL_CONNECTOR_SUFFIX) === false
                    && strpos($connector, DictionaryConnectorInterface::DICTIONARY_CONNECTOR_SUFFIX) === false;
            };
        }

        return parent::processConnectors($integration, $parameters, $callback);
    }

    /**
     * @param Integration $integration
     *
     * @return bool
     */
    protected function isInitialJobRunning(Integration $integration)
    {
        $initialJobsRunning = $this->getChannelRepository()->getExistingSyncJobsCount(
            InitialSyncCommand::COMMAND_NAME,
            $integration->getId()
        );

        return $initialJobsRunning > 0;
    }

    /**
     * In case when initial sync does not started yet, it failed or start sync date was changed - run initial sync.
     *
     * @param Integration $integration
     */
    protected function scheduleInitialSyncIfRequired(Integration $integration)
    {
        $this->saveInitialSyncStartDate($integration);
        /** @var MagentoSoapTransport $transport */
        $transport = $integration->getTransport();
        if (!$this->isInitialJobRunning($integration)
            && $this->isInitialSyncRequired(
                $integration,
                $transport->getInitialSyncStartDate(),
                $transport->getSyncStartDate()
            )
        ) {
            $this->logger->info('Scheduling initial synchronization');
            $job = new Job(
                InitialSyncCommand::COMMAND_NAME,
                [
                    sprintf('--integration-id=%s', $integration->getId()),
                    '--skip-dictionary',
                    '-v'
                ]
            );
            $this->saveEntity($job);
        }
    }

    /**
     * Save initial sync start date and flag initial sync as started.
     *
     * @param Integration $integration
     */
    protected function saveInitialSyncStartDate(Integration $integration)
    {
        if (!$this->isInitialSyncStarted($integration)) {
            /** @var MagentoSoapTransport $transport */
            $transport = $integration->getTransport();
            $initialSyncStartDate = $this->getInitialSyncStartDate($integration);
            $transport->setInitialSyncStartDate($initialSyncStartDate);
            $this->saveEntity($transport);
        }
    }

    /**
     * @param object $entity
     *
     * @return Integration
     */
    protected function reloadEntity($entity)
    {
        return $this->doctrineHelper->getEntity(
            $this->doctrineHelper->getEntityClass($entity),
            $this->doctrineHelper->getEntityIdentifier($entity)
        );
    }

    /**
     * Reset connector statuses and transport initial sync start date.
     *
     * @param Integration $integration
     */
    protected function forceSync(Integration $integration)
    {
        $connectors = $integration->getConnectors();
        foreach ($connectors as $connectorName) {
            $connector = $this->registry->getConnectorType($integration->getType(), $connectorName);
            if ($connector instanceof ForceConnectorInterface && $connector->supportsForceSync()) {
                $this->markConnectorSyncStatusesSkipped($integration, $connectorName);
            }
        }

        /** @var MagentoSoapTransport $transport */
        $transport = $integration->getTransport();
        $transport->setInitialSyncStartDate(null);
        $this->saveEntity($transport);
    }

    /**
     * Mark connector statuses as skipped for further checks.
     *
     * @param Integration $integration
     * @param string      $connectorName
     */
    protected function markConnectorSyncStatusesSkipped(Integration $integration, $connectorName)
    {
        /** @var EntityManager $em */
        $em = $this->doctrineRegistry->getManager();

        $processedStatuses = [];
        $statusesIterator = $this->getChannelRepository()->getConnectorStatuses($integration, $connectorName);
        foreach ($statusesIterator as $status) {
            $processedStatuses[] = $status;
            $statusData = $status->getData();
            $statusData[self::SKIP_STATUS] = true;
            $status->setData($statusData);

            if (count($processedStatuses) === ChannelRepository::BUFFER_SIZE) {
                $em->flush($processedStatuses);
                $processedStatuses = [];
            }
        }

        if ($processedStatuses) {
            $em->flush($processedStatuses);
        }
    }
}
