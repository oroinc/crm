<?php

namespace Oro\Bundle\MagentoBundle\Provider\Connector;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Exception\RuntimeException;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Oro\Bundle\MagentoBundle\Provider\AbstractInitialProcessor;
use Oro\Bundle\MagentoBundle\Provider\BatchFilterBag;
use Oro\Bundle\MagentoBundle\Provider\InitialSyncProcessor;
use Oro\Bundle\MagentoBundle\Provider\Iterator\PredefinedFiltersAwareInterface;
use Oro\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Psr\Log\LoggerAwareInterface;

abstract class AbstractMagentoConnector extends AbstractConnector implements MagentoConnectorInterface
{
    const LAST_SYNC_KEY = 'lastSyncItemDate';

    /** @var MagentoTransportInterface */
    protected $transport;

    /** @var array */
    protected $bundleConfiguration;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var string */
    protected $className;

    /**
     * @param ContextRegistry          $contextRegistry
     * @param LoggerStrategy           $logger
     * @param ConnectorContextMediator $contextMediator
     * @param array                    $bundleConfiguration
     */
    public function __construct(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        ConnectorContextMediator $contextMediator,
        array $bundleConfiguration
    ) {
        parent::__construct($contextRegistry, $logger, $contextMediator);
        $this->bundleConfiguration = $bundleConfiguration;
    }

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function setManagerRegistry(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        if (!$this->className) {
            throw new \InvalidArgumentException(sprintf('Entity FQCN is missing for "%s" connector', $this->getType()));
        }

        return $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $item = parent::read();

        if (null !== $item) {
            $this->addStatusData(
                self::LAST_SYNC_KEY,
                $this->getMaxUpdatedDate($this->getUpdatedDate($item), $this->getStatusData(self::LAST_SYNC_KEY))
            );
        }
        $iterator = $this->getSourceIterator();
        if (!$iterator->valid() && $iterator instanceof UpdatedLoaderInterface) {
            // cover case, when no one item was synced
            // then just take point from what it was started
            $dateFromReadStarted = $iterator->getStartDate() ? $iterator->getStartDate()->format('Y-m-d H:i:s') : null;
            $this->addStatusData(
                self::LAST_SYNC_KEY,
                $this->getMaxUpdatedDate($this->getStatusData(self::LAST_SYNC_KEY), $dateFromReadStarted)
            );
        }

        return $item;
    }

    /**
     * @param ContextInterface $context
     */
    protected function initializeTransport(ContextInterface $context)
    {
        $this->channel   = $this->contextMediator->getChannel($context);
        $this->transport = $this->contextMediator->getInitializedTransport($this->channel, true);

        $this->validateConfiguration();
        $this->setSourceIterator($this->getConnectorSource());

        $sourceIterator = $this->getSourceIterator();
        if ($sourceIterator instanceof LoggerAwareInterface) {
            $sourceIterator->setLogger($this->logger);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        $this->initializeTransport($context);

        // set start date and mode depending on status
        /** @var Status $status */
        $status = $this->getLastCompletedIntegrationStatus($this->channel, $this->getType());
        $iterator = $this->getSourceIterator();

        if ($iterator instanceof UpdatedLoaderInterface) {
            $startDate = $this->getStartDate($status);

            if ($status) {
                // use assumption interval in order to prevent mistiming issues
                $intervalString = $this->bundleConfiguration['sync_settings']['mistiming_assumption_interval'];
                $this->logger->debug(sprintf('Real start date: "%s"', $startDate->format(\DateTime::RSS)));
                $this->logger->debug(sprintf('Subtracted the presumable mistiming interval "%s"', $intervalString));
                $startDate->sub(\DateInterval::createFromDateString($intervalString));
            }

            $executionContext = $this->stepExecution->getJobExecution()->getExecutionContext();
            $interval = $executionContext->get(InitialSyncProcessor::INTERVAL);
            if ($interval) {
                $iterator->setMode(UpdatedLoaderInterface::IMPORT_MODE_INITIAL);
                $iterator->setSyncRange($interval);
            }

            $incrementalInterval = $executionContext->get(InitialSyncProcessor::INCREMENTAL_INTERVAL);
            if ($incrementalInterval) {
                $iterator->setSyncRange($incrementalInterval);
            }

            $minimalSyncDate = $executionContext->get(InitialSyncProcessor::START_SYNC_DATE);
            if ($minimalSyncDate) {
                $iterator->setMinSyncDate($minimalSyncDate);
            }

            $iterator->setStartDate($startDate);
        }

        // pass filters from connector
        $this->setPredefinedFilters($context, $iterator);
    }

    /**
     * @param Status $status
     *
     * @return \DateTime
     */
    protected function getStartDate(Status $status = null)
    {
        $jobContext = $this->stepExecution->getJobExecution()->getExecutionContext();
        $initialSyncedTo = $jobContext->get(InitialSyncProcessor::SYNCED_TO);
        if ($initialSyncedTo) {
            return $initialSyncedTo;
        }

        // If connector has status use it's information for start date
        if ($status) {
            $data = $status->getData();
            if (empty($data[AbstractInitialProcessor::SKIP_STATUS])) {
                if (!empty($data[self::LAST_SYNC_KEY])) {
                    return new \DateTime($data[self::LAST_SYNC_KEY], new \DateTimeZone('UTC'));
                }

                return clone $status->getDate();
            }
        }

        // If there is no status and LAST_SYNC_KEY is present in contexts use it
        $lastSyncDate = $this->stepExecution->getExecutionContext()->get(self::LAST_SYNC_KEY);
        if ($lastSyncDate) {
            return $lastSyncDate;
        } elseif ($jobContext->get(self::LAST_SYNC_KEY)) {
            return $jobContext->get(self::LAST_SYNC_KEY);
        }

        return new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Get last completed status for connector of integration instance.
     *
     * @param Integration $integration
     * @param string $connector
     * @return Status|null
     */
    protected function getLastCompletedIntegrationStatus(Integration $integration, $connector)
    {
        if (!$this->managerRegistry) {
            throw new RuntimeException('Doctrine manager registry is not initialized. Use setManagerRegistry method.');
        }

        return $this->managerRegistry->getRepository('OroIntegrationBundle:Channel')
            ->getLastStatusForConnector($integration, $connector, Status::STATUS_COMPLETED);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateConfiguration()
    {
        parent::validateConfiguration();

        if (!$this->transport instanceof MagentoTransportInterface) {
            throw new \LogicException('Option "transport" should implement "MagentoTransportInterface"');
        }
    }

    /**
     * @param string|null $currDateToCompare
     * @param string|null $prevDateToCompare
     *
     * @return null|string
     */
    protected function getMaxUpdatedDate($currDateToCompare, $prevDateToCompare)
    {
        if (!$prevDateToCompare) {
            $date = $currDateToCompare;
        } elseif (!$currDateToCompare) {
            $date = $prevDateToCompare;
        } else {
            $date = strtotime($currDateToCompare) > strtotime($prevDateToCompare)
                ? $currDateToCompare
                : $prevDateToCompare;
        }

        return $date ? $this->getMinUpdatedDate($date) : $date;
    }

    /**
     * Compares maximum updated date with current date and returns the smallest.
     *
     * @param string $updatedDate
     *
     * @return string
     */
    protected function getMinUpdatedDate($updatedDate)
    {
        $currentDate = new \DateTime('now', new \DateTimeZone('UTC'));
        if ($currentDate->getTimestamp() > strtotime($updatedDate)) {
            return $updatedDate;
        }

        return $currentDate->format('Y-m-d H:i:s');
    }

    /**
     * @param array $item
     *
     * @return string|null
     */
    protected function getUpdatedDate(array $item)
    {
        switch (true) {
            case !empty($item['updatedAt']):
                return $item['updatedAt'];
                break;
            case !empty($item['updated_at']):
                return $item['updated_at'];
                break;
            default:
                return null;
        }
    }

    /**
     * @param ContextInterface $context
     * @param \Iterator $iterator
     */
    protected function setPredefinedFilters(ContextInterface $context, \Iterator $iterator)
    {
        $filters = null;
        if ($context->hasOption('filters')) {
            $filters = $context->getOption('filters');
            $context->removeOption('filters');
        }

        $complexFilters = null;
        if ($context->hasOption('complex_filters')) {
            $complexFilters = $context->getOption('complex_filters');
            $context->removeOption('complex_filters');
        }

        if ($filters || $complexFilters) {
            if ($iterator instanceof PredefinedFiltersAwareInterface) {
                $predefinedFilters = new BatchFilterBag((array)$filters, (array)$complexFilters);

                $iterator->setPredefinedFiltersBag($predefinedFilters);
            } else {
                throw new \LogicException('Iterator does not support predefined filters');
            }
        }
    }
}
