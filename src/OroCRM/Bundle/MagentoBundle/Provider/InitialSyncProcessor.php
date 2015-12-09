<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\ImportExport\Job\Executor;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\SyncProcessor;

class InitialSyncProcessor extends AbstractInitialProcessor
{
    const INITIAL_CONNECTOR_SUFFIX = '_initial';

    /** @var array|null */
    protected $bundleConfiguration;

    /** @var SyncProcessor[] */
    protected $postProcessors = [];

    /** @var bool */
    protected $dictionaryDataLoaded = false;

    /**
     * @param ManagerRegistry $doctrineRegistry
     * @param ProcessorRegistry $processorRegistry
     * @param Executor $jobExecutor
     * @param TypesRegistry $registry
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerStrategy $logger
     * @param array $bundleConfiguration
     */
    public function __construct(
        ManagerRegistry $doctrineRegistry,
        ProcessorRegistry $processorRegistry,
        Executor $jobExecutor,
        TypesRegistry $registry,
        EventDispatcherInterface $eventDispatcher,
        LoggerStrategy $logger = null,
        array $bundleConfiguration = null
    ) {
        parent::__construct(
            $doctrineRegistry,
            $processorRegistry,
            $jobExecutor,
            $registry,
            $eventDispatcher,
            $logger
        );

        $this->bundleConfiguration = $bundleConfiguration;
    }

    /**
     * @param string $connectorType
     * @param SyncProcessor $processor
     * @return InitialSyncProcessor
     */
    public function addPostProcessor($connectorType, SyncProcessor $processor)
    {
        $this->postProcessors[$connectorType] = $processor;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function processDictionaryConnectors(Integration $integration)
    {
        if (!$this->dictionaryDataLoaded) {
            parent::processDictionaryConnectors($integration);

            $this->dictionaryDataLoaded = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function processConnectors(Integration $integration, array $parameters = [], callable $callback = null)
    {
        if (empty($parameters['skip-dictionary'])) {
            $this->processDictionaryConnectors($integration);
        }

        // Set start date for initial connectors
        $startSyncDate = $integration->getTransport()->getSettingsBag()->get('start_sync_date');
        $parameters[self::START_SYNC_DATE] = $startSyncDate;

        // Pass interval to connectors for further filters creation
        $interval = $this->getSyncInterval();
        $parameters[self::INTERVAL] = $interval;

        // Collect initial connectors
        $postProcessConnectorTypes = array_keys($this->postProcessors);
        $connectors = $this->getTypesOfConnectorsToProcess($integration, $this->getConnectorsFilterFunction($callback));
        $postProcessConnectors = array_intersect($connectors, $postProcessConnectorTypes);
        $connectors = array_diff($connectors, $postProcessConnectorTypes);

        /** @var \DateTime[] $connectorsSyncedTo */
        $connectorsSyncedTo = [];
        foreach ($connectors as $connector) {
            $connectorsSyncedTo[$connector] = $this->getInitialConnectorSyncedTo($integration, $connector);
        }

        // Process all initial connectors by date interval while there are connectors to process
        $isSuccess = true;
        do {
            $syncedConnectors = 0;
            foreach ($connectors as $connector) {
                if ($connectorsSyncedTo[$connector] > $startSyncDate) {
                    $syncedConnectors++;

                    $this->logger->info(
                        sprintf(
                            'Syncing connector %s starting %s interval %s',
                            $connector,
                            $connectorsSyncedTo[$connector]->format('Y-m-d H:i:s'),
                            $interval->format('%d days')
                        )
                    );

                    try {
                        // Pass synced to for further filters creation
                        $parameters = array_merge(
                            $parameters,
                            [self::INITIAL_SYNCED_TO => clone $connectorsSyncedTo[$connector]]
                        );

                        $realConnector = $this->getRealConnector($integration, $connector);
                        $status = $this->processIntegrationConnector(
                            $integration,
                            $realConnector,
                            $parameters
                        );
                        // Move sync date into past by interval value
                        $connectorsSyncedTo[$connector]->sub($interval);

                        $isSuccess = $isSuccess && $this->isIntegrationConnectorProcessSuccess($status);

                        if ($isSuccess) {
                            // Save synced to date for connector
                            $syncedTo = $connectorsSyncedTo[$connector];
                            if ($syncedTo < $startSyncDate) {
                                $syncedTo = $startSyncDate;
                            }
                            $this->updateSyncedTo($integration, $connector, $syncedTo);
                        } else {
                            break 2;
                        }
                    } catch (\Exception $e) {
                        $isSuccess = false;

                        $this->logger->critical($e->getMessage());
                        break 2;
                    }
                }
            }
        } while ($syncedConnectors > 0);

        if ($isSuccess && $postProcessConnectors) {
            $isSuccess = $this->executePostProcessConnectors(
                $integration,
                $parameters,
                $postProcessConnectors,
                $startSyncDate
            );
        }

        return $isSuccess;
    }

    /**
     * @param callable|null $callback
     * @return \Closure
     */
    protected function getConnectorsFilterFunction(callable $callback = null)
    {
        return function ($connector) use ($callback) {
            if (is_callable($callback) && !call_user_func($callback, $connector)) {
                return false;
            }

            return strpos($connector, self::INITIAL_CONNECTOR_SUFFIX) !== false;
        };
    }

    /**
     * @param Integration $integration
     * @param string $connector
     * @param \DateTime $syncedTo
     */
    protected function updateSyncedTo(Integration $integration, $connector, \DateTime $syncedTo)
    {
        $formattedSyncedTo = $syncedTo->format(\DateTime::ISO8601);

        $lastStatus = $this->getLastStatusForConnector($integration, $connector, Status::STATUS_COMPLETED);
        $statusData = $lastStatus->getData();
        $statusData[self::INITIAL_SYNCED_TO] = $formattedSyncedTo;
        $lastStatus->setData($statusData);

        $this->addConnectorStatusAndFlush($integration, $lastStatus);
    }

    /**
     * @param Integration $integration
     * @param string $connector
     * @return \DateTime
     */
    protected function getInitialConnectorSyncedTo(Integration $integration, $connector)
    {
        $latestSyncedTo = $this->getSyncedTo($integration, $connector);
        if ($latestSyncedTo === false) {
            return clone $this->getInitialSyncStartDate($integration);
        }

        return clone $latestSyncedTo;
    }

    /**
     * @return \DateInterval
     */
    protected function getSyncInterval()
    {
        if (empty($this->bundleConfiguration['sync_settings']['initial_import_step_interval'])) {
            throw new \InvalidArgumentException('Option "initial_import_step_interval" is missing');
        }

        $syncInterval = $this->bundleConfiguration['sync_settings']['initial_import_step_interval'];
        $interval = \DateInterval::createFromDateString($syncInterval);

        return $interval;
    }

    /**
     * @param Integration $integration
     * @param array $parameters
     * @param array $postProcessConnectors
     * @param \DateTime $startSyncDate
     * @return bool
     */
    protected function executePostProcessConnectors(
        Integration $integration,
        array $parameters,
        array $postProcessConnectors,
        \DateTime $startSyncDate
    ) {
        $isSuccess = true;
        foreach ($postProcessConnectors as $connectorType) {
            // Do not sync already synced connectors
            if ($this->getLastStatusForConnector($integration, $connectorType, Status::STATUS_COMPLETED)) {
                continue;
            }

            $processor = $this->postProcessors[$connectorType];
            $isSuccess = $isSuccess && $processor->process($integration, $connectorType, $parameters);
            if ($isSuccess) {
                $this->updateSyncedTo($integration, $connectorType, $startSyncDate);
            }
        }

        return $isSuccess;
    }
}
