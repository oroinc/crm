<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\PredefinedFiltersAwareInterface;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

abstract class AbstractMagentoConnector extends AbstractConnector implements MagentoConnectorInterface
{
    /** @var MagentoTransportInterface */
    protected $transport;

    /** @var array */
    protected $bundleConfiguration;

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
     * {@inheritdoc}
     */
    public function read()
    {
        $item = parent::read();

        if (null !== $item) {
            $this->addStatusData(
                'lastSyncItemDate',
                $this->getMaxUpdatedDate($this->getUpdatedDate($item), $this->getStatusData('lastSyncItemDate'))
            );
        }
        $iterator = $this->getSourceIterator();
        if (!$iterator->valid() && $iterator instanceof UpdatedLoaderInterface) {
            // cover case, when no one item was synced
            // then just take point from what it was started
            $dateFromReadStarted = $iterator->getStartDate() ? $iterator->getStartDate()->format('Y-m-d H:i:s') : null;
            $this->addStatusData(
                'lastSyncItemDate',
                $this->getMaxUpdatedDate($this->getStatusData('lastSyncItemDate'), $dateFromReadStarted)
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
        $status      = $this->channel->getStatusesForConnector($this->getType(), Status::STATUS_COMPLETED)->first();
        $iterator    = $this->getSourceIterator();
        $isForceSync = $context->getOption('force') && $this->supportsForceSync();

        if ($iterator instanceof UpdatedLoaderInterface && !empty($status) && !$isForceSync) {
            /** @var Status $status */
            $iterator->setMode(UpdatedLoaderInterface::IMPORT_MODE_UPDATE);
            $data = $status->getData();

            if (!empty($data['lastSyncItemDate'])) {
                $startDate = new \DateTime($data['lastSyncItemDate'], new \DateTimeZone('UTC'));
            } else {
                $startDate = clone $status->getDate();
            }

            // use assumption interval in order to prevent mistiming issues
            $intervalString = $this->bundleConfiguration['sync_settings']['mistiming_assumption_interval'];
            $this->logger->debug(sprintf('Real start date: "%s"', $startDate->format(\DateTime::RSS)));
            $this->logger->debug(sprintf('Subtracted the presumable mistiming interval "%s"', $intervalString));
            $startDate->sub(\DateInterval::createFromDateString($intervalString));
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
