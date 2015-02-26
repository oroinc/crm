<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

class InitialSyncProcessor extends AbstractInitialProcessor
{
    const INITIAL_CONNECTOR_SUFFIX = '_initial';

    // TODO: Read from bundle configuration
    /** @var array */
    protected $bundleConfiguration = [
        'sync_settings' => [
            'initial_import_step_interval' => '1 day'
        ]
    ];

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

        $interval = $this->getSyncInterval();
        $parameters[self::INTERVAL] = $interval;

        $connectors = $this->getConnectorsToSync($integration, $callback);
        /** @var \DateTime[] $connectorsSyncedTo */
        $connectorsSyncedTo = [];
        foreach ($connectors as $connector) {
            $connectorsSyncedTo[$connector] = $this->getInitialConnectorSyncedTo($integration, $connector);
        }

        $isSuccess = true;
        do {
            $syncedConnectors = 0;
            foreach ($connectors as $connector) {
                if ($connectorsSyncedTo[$connector] > $startSyncDate) {
                    $syncedConnectors++;

                    try {
                        $parameters = array_merge(
                            $parameters,
                            [self::INITIAL_SYNCED_TO => $connectorsSyncedTo[$connector]]
                        );
                        $result = $this->processIntegrationConnector(
                            $integration,
                            $connector,
                            $parameters
                        );
                        $connectorsSyncedTo[$connector]->sub($interval);

                        $isSuccess = $isSuccess && $result;
                    } catch (\Exception $e) {
                        $isSuccess = false;

                        $this->logger->critical($e->getMessage());
                        break;
                    }
                }
            }
        } while ($syncedConnectors > 0);

        return $isSuccess;
    }

    /**
     * {@inheritdoc}
     */
    protected function processImport($connector, $jobName, $configuration, Integration $integration)
    {
        $isSuccess = parent::processImport($connector, $jobName, $configuration, $integration);

        // Save synced to date for further checks in InitialScheduleProcessor
        $syncedTo = $this->getSyncedTo($integration, $connector);
        if ($syncedTo) {
            $integration->getSynchronizationSettings()
                ->offsetSet(self::INITIAL_SYNCED_TO, $syncedTo->format(\DateTime::ISO8601));
            $this->saveEntity($integration);
        }

        return $isSuccess;
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
     * @param Integration $integration
     * @param string $connector
     * @return bool|\DateTime
     */
    protected function getSyncedTo(Integration $integration, $connector)
    {
        $lastStatus = $this->doctrineRegistry->getRepository('OroIntegrationBundle:Channel')
            ->getLastStatusForConnector($integration, $connector);
        if ($lastStatus) {
            $statusData = $lastStatus->getData();
            if (!empty($statusData[self::INITIAL_SYNCED_TO])) {
                return \DateTime::createFromFormat(
                    \DateTime::ISO8601,
                    $statusData[self::INITIAL_SYNCED_TO],
                    new \DateTimeZone('UTC')
                );
            }
        }

        return false;
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
