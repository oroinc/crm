<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\ImportExport\Job\Executor;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;

class InitialSyncProcessor extends AbstractInitialProcessor
{
    const INITIAL_CONNECTOR_SUFFIX = '_initial';

    /** @var array|null */
    protected $bundleConfiguration;

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
     * {@inheritdoc}
     */
    protected function processConnectors(Integration $integration, array $parameters = [], callable $callback = null)
    {
        $callback = function ($connector) {
            return strpos($connector, self::INITIAL_CONNECTOR_SUFFIX) !== false;
        };

        // Set start date for initial connectors
        $startSyncDate = $integration->getTransport()->getSettingsBag()->get('start_sync_date');
        $parameters[self::START_SYNC_DATE] = $startSyncDate;

        // Pass interval to connectors for further filters creation
        $interval = $this->getSyncInterval();
        $parameters[self::INTERVAL] = $interval;

        // Collect initial connectors
        $connectors = $this->getConnectorsToSync($integration, $callback);
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
                            [self::INITIAL_SYNCED_TO => $connectorsSyncedTo[$connector]]
                        );
                        $result = $this->processIntegrationConnector(
                            $integration,
                            $connector,
                            $parameters
                        );
                        // Move sync date into past by interval value
                        $connectorsSyncedTo[$connector]->sub($interval);

                        $isSuccess = $isSuccess && $result;

                        if ($isSuccess) {
                            // Save synced to date for connector
                            $this->updateSyncedTo($integration, $connector, $connectorsSyncedTo[$connector]);
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

        return $isSuccess;
    }

    /**
     * @param Integration $integration
     * @param string $connector
     * @param \DateTime $syncedTo
     */
    protected function updateSyncedTo(Integration $integration, $connector, \DateTime $syncedTo)
    {
        $formattedSyncedTo = $syncedTo->format(\DateTime::ISO8601);

        $lastStatus = $this->getLastStatusForConnector($integration, $connector);
        $statusData = $lastStatus->getData();
        $statusData[self::INITIAL_SYNCED_TO] = $formattedSyncedTo;
        $lastStatus->setData($statusData);

        $this->getChannelRepository()->addStatus($integration, $lastStatus);
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
            return $this->getInitialSyncStartDate($integration);
        }

        return $latestSyncedTo;
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
}
