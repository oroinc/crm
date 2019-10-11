<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\ImportExport\Job\Executor;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\SyncProcessor;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Sync processor for magento integration.
 */
class MagentoSyncProcessor extends SyncProcessor
{
    const SYNCED_TO = 'initialSyncedTo';
    const SKIP_STATUS = 'skip';
    const INTERVAL = 'initialSyncInterval';
    const INCREMENTAL_INTERVAL = 'incrementalInterval';
    const START_SYNC_DATE = 'start_sync_date';

    /** @var array|null */
    protected $bundleConfiguration;

    /** @var string */
    protected $channelClassName;

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
     * @param string $channelClassName
     */
    public function setChannelClassName($channelClassName)
    {
        $this->channelClassName = $channelClassName;
    }

    /**
     * @return \DateInterval
     */
    protected function getSyncInterval()
    {
        if (empty($this->bundleConfiguration['sync_settings']['import_step_interval'])) {
            throw new \InvalidArgumentException('Option "import_step_interval" is missing');
        }

        $syncInterval = $this->bundleConfiguration['sync_settings']['import_step_interval'];
        $interval = \DateInterval::createFromDateString($syncInterval);

        return $interval;
    }

    /**
     * {@inheritdoc}
     */
    protected function processConnectors(Integration $integration, array $parameters = [], callable $callback = null)
    {
        // Pass interval to connectors for further filters creation
        $interval = $this->getSyncInterval();
        $parameters[self::INCREMENTAL_INTERVAL] = $interval;

        // Collect initial connectors
        $connectors = $this->getTypesOfConnectorsToProcess($integration, $callback);

        /** @var \DateTime[] $connectorsSyncedTo */
        $connectorsSyncedTo = [];
        foreach ($connectors as $connector) {
            $connectorsSyncedTo[$connector] = $this->getConnectorSyncedTo($integration, $connector);
        }

        $processedConnectorStatuses = [];
        $isSuccess = true;

        foreach ($connectors as $connector) {
            $this->logger->info(
                sprintf(
                    'Syncing connector %s starting %s interval %s',
                    $connector,
                    $connectorsSyncedTo[$connector]->format('Y-m-d H:i:s'),
                    $interval->format('%d days')
                )
            );

            try {
                $realConnector = $this->getRealConnector($integration, $connector);
                if (!$this->isConnectorAllowed($realConnector, $integration, $processedConnectorStatuses)) {
                    continue;
                }
                // Pass synced to for further filters creation
                $parameters = array_merge(
                    $parameters,
                    [self::SYNCED_TO => clone $connectorsSyncedTo[$connector]]
                );

                $status = $this->processIntegrationConnector(
                    $integration,
                    $realConnector,
                    $parameters
                );
                // Move sync date into future by interval value
                $connectorsSyncedTo[$connector] = $this->getIncrementalSyncedTo(
                    $connectorsSyncedTo[$connector],
                    $interval
                );
                $isSuccess = $isSuccess && $this->isIntegrationConnectorProcessSuccess($status);

                if ($isSuccess) {
                    // Save synced to date for connector
                    $syncedTo = $connectorsSyncedTo[$connector];
                    $this->updateSyncedTo($integration, $connector, $syncedTo);
                }
            } catch (\Exception $e) {
                $isSuccess = false;
                $this->logger->critical($e->getMessage(), ['exception' => $e]);
            }
        }

        return $isSuccess;
    }

    /**
     * @param \DateTime $syncedTo
     * @param \DateInterval $interval
     * @return \DateTime
     */
    protected function getIncrementalSyncedTo(\DateTime $syncedTo, \DateInterval $interval): \DateTime
    {
        $syncedTo->add($interval);
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        if ($syncedTo > $now) {
            return $now;
        }

        return $syncedTo;
    }

    /**
     * @param Integration $integration
     * @param string $connector
     * @return \DateTime
     */
    protected function getConnectorSyncedTo(Integration $integration, $connector)
    {
        $latestSyncedTo = $this->getSyncedTo($integration, $connector);
        if ($latestSyncedTo === false) {
            return clone $this->getInitialSyncStartDate($integration);
        }

        return clone $latestSyncedTo;
    }

    /**
     * @param Integration $integration
     * @return \DateTime
     */
    protected function getInitialSyncStartDate(Integration $integration)
    {
        if ($this->isInitialSyncStarted($integration)) {
            /** @var MagentoTransport $transport */
            $transport = $integration->getTransport();

            return $transport->getInitialSyncStartDate();
        } else {
            return new \DateTime('now', new \DateTimeZone('UTC'));
        }
    }

    /**
     * @param Integration $integration
     * @return bool
     */
    protected function isInitialSyncStarted(Integration $integration)
    {
        /** @var MagentoTransport $transport */
        $transport = $integration->getTransport();

        return (bool)$transport->getInitialSyncStartDate();
    }

    /**
     * @param Integration $integration
     * @param string $connector
     * @return bool|\DateTime
     */
    protected function getSyncedTo(Integration $integration, $connector)
    {
        $lastStatus = $this->getLastStatusForConnector($integration, $connector, Status::STATUS_COMPLETED);
        if ($lastStatus) {
            $statusData = $lastStatus->getData();
            if (!empty($statusData[static::SYNCED_TO])) {
                return \DateTime::createFromFormat(
                    \DateTime::ISO8601,
                    $statusData[static::SYNCED_TO],
                    new \DateTimeZone('UTC')
                );
            }
        }

        return false;
    }

    /**
     * @param Integration $integration
     * @param string $connector
     * @param int|null $code
     * @return null|Status
     */
    protected function getLastStatusForConnector(Integration $integration, $connector, $code = null)
    {
        $status = $this->getChannelRepository()->getLastStatusForConnector($integration, $connector, $code);
        if ($status) {
            $statusData = $status->getData();
            if (!empty($statusData[self::SKIP_STATUS])) {
                return null;
            }
        }

        return $status;
    }

    /**
     * @return ChannelRepository
     */
    protected function getChannelRepository()
    {
        if (!$this->channelClassName) {
            throw new \InvalidArgumentException('Channel class option is missing');
        }

        return $this->doctrineRegistry->getRepository($this->channelClassName);
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
        $statusData[self::SYNCED_TO] = $formattedSyncedTo;
        $lastStatus->setData($statusData);

        $this->addConnectorStatusAndFlush($integration, $lastStatus);
    }
}
