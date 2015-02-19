<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Exception\RuntimeException;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\PredefinedFiltersAwareInterface;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

abstract class AbstractMagentoConnector extends AbstractConnector implements MagentoConnectorInterface
{
    const LAST_SYNC_KEY = 'lastSyncItemDate';

    /** @var MagentoTransportInterface */
    protected $transport;

    /** @var array */
    protected $bundleConfiguration;

    /** @var ManagerRegistry */
    protected $managerRegistry;

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
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        parent::initializeFromContext($context);

        // set start date and mode depending on status
        /** @var Status $status */
        $status = $this->getLastCompletedIntegrationStatus($this->channel, $this->getType());
        $iterator = $this->getSourceIterator();
        $isForceSync = $context->getOption('force') && $this->supportsForceSync();

        if ($iterator instanceof UpdatedLoaderInterface && !$isForceSync) {
            $startDate = $this->getStartDate($status);

            if ($status) {
                $iterator->setMode(UpdatedLoaderInterface::IMPORT_MODE_UPDATE);

                // use assumption interval in order to prevent mistiming issues
                $intervalString = $this->bundleConfiguration['sync_settings']['mistiming_assumption_interval'];
                $this->logger->debug(sprintf('Real start date: "%s"', $startDate->format(\DateTime::RSS)));
                $this->logger->debug(sprintf('Subtracted the presumable mistiming interval "%s"', $intervalString));
                $startDate->sub(\DateInterval::createFromDateString($intervalString));
            }

            $iterator->setStartDate($startDate);
        }

        // pass filters from connector
        if ($context->hasOption('filters') || $context->hasOption('complex_filters')) {
            if ($iterator instanceof PredefinedFiltersAwareInterface) {
                $filters           = $context->getOption('filters') ?: [];
                $complexFilters    = $context->getOption('complex_filters') ?: [];
                $predefinedFilters = new BatchFilterBag($filters, $complexFilters);

                $iterator->setPredefinedFiltersBag($predefinedFilters);
            } else {
                throw new \LogicException('Iterator does not support predefined filters');
            }
        }
    }

    /**
     * @param Status $status
     *
     * @return \DateTime
     */
    protected function getStartDate(Status $status = null)
    {
        $jobContext = $this->stepExecution->getJobExecution()->getExecutionContext();
        $initialSyncedTo = $jobContext->get(InitialSyncProcessor::INITIAL_SYNCED_TO);
        if ($initialSyncedTo) {
            return $initialSyncedTo;
        }

        $lastSyncDate = $this->stepExecution->getExecutionContext()->get(self::LAST_SYNC_KEY);
        if ($lastSyncDate) {
            return $lastSyncDate;
        }

        if ($status) {
            $data = $status->getData();
            if (!empty($data[self::LAST_SYNC_KEY])) {
                return new \DateTime($data[self::LAST_SYNC_KEY], new \DateTimeZone('UTC'));
            }

            return clone $status->getDate();
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
            return $currDateToCompare;
        } elseif (!$currDateToCompare) {
            return $prevDateToCompare;
        }

        return strtotime($currDateToCompare) > strtotime($prevDateToCompare) ? $currDateToCompare : $prevDateToCompare;
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
}
