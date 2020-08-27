<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Provider\SyncProcessor;

/**
 * Handles initial stages of the data sync.
 */
class InitialSyncProcessor extends AbstractInitialProcessor
{
    const INITIAL_CONNECTOR_SUFFIX = '_initial';

    /** @var SyncProcessor[] */
    protected $postProcessors = [];

    /** @var bool */
    protected $dictionaryDataLoaded = false;

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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function processConnectors(Integration $integration, array $parameters = [], callable $callback = null)
    {
        if (empty($parameters['skip-dictionary'])) {
            $this->processDictionaryConnectors($integration);
        }

        // Set start date for initial connectors
        $startSyncDate = $integration->getTransport()->getSettingsBag()->get(self::START_SYNC_DATE);
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
            $connectorsSyncedTo[$connector] = $this->getConnectorSyncedTo($integration, $connector);
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
                            [self::SYNCED_TO => clone $connectorsSyncedTo[$connector]]
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

                        $this->logger->critical($e->getMessage(), ['exception' => $e]);
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
