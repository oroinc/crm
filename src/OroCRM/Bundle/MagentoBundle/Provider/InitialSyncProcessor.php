<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

class InitialSyncProcessor extends AbstractInitialProcessor
{
    const INITIAL_CONNECTOR_SUFFIX = '_initial';

    /**
     * {@inheritdoc}
     */
    protected function processConnectors(Integration $integration, array $parameters = [], callable $callback = null)
    {
        $callback = function ($connector) {
            return strpos($connector, self::INITIAL_CONNECTOR_SUFFIX) !== false;
        };

        return parent::processConnectors($integration, $parameters, $callback);
    }

    /**
     * {@inheritdoc}
     */
    protected function processImport($connector, $jobName, $configuration, Integration $integration)
    {
        // Set start date for initial connectors
        $initialConnectorSyncedTo = $this->getInitialConnectorSyncedTo($integration, $connector);
        $startSyncDate = $integration->getTransport()->getSettingsBag()->get('start_sync_date');
        $configuration[ProcessorRegistry::TYPE_IMPORT][self::INITIAL_SYNCED_TO] = $initialConnectorSyncedTo;
        $configuration[ProcessorRegistry::TYPE_IMPORT][self::START_SYNC_DATE] = $startSyncDate;

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
}
