<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

/**
 * @TODO FIXME should be refactored to use only call of transport with generalized filter
 */
abstract class AbstractApiBasedConnector extends AbstractConnector implements MagentoConnectorInterface
{
    const DEFAULT_SYNC_RANGE = '1 month';

    /** @var \DateTime */
    protected $lastSyncDate;

    /** @var \DateInterval */
    protected $syncRange;

    /** @var StoreConnector */
    protected $storeConnector;

    /** @var ChannelRepository */
    protected $channelRepository;

    /** @var array */
    protected $entitiesIdsBuffer = [];

    /** @var array dependencies data: customer groups, stores, websites */
    protected $dependencies = [];

    /** @var int */
    protected $batchSize;

    /**
     * @param ContextRegistry             $contextRegistry
     * @param LoggerStrategy              $logger
     * @param \Doctrine\ORM\EntityManager $em
     * @param StoreConnector              $storeConnector
     */
    public function __construct(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        EntityManager $em,
        StoreConnector $storeConnector
    ) {
        parent::__construct($contextRegistry, $logger);
        $this->channelRepository = $em->getRepository('OroIntegrationBundle:Channel');
        $this->storeConnector = $storeConnector;
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeFromContext(ContextInterface $context)
    {
        $this->dependencies      = [];
        $this->entitiesIdsBuffer = [];
        parent::initializeFromContext($context);
        $settings = $this->transportSettings->all();

        $startSyncDateKey = 'start_sync_date';
        if (empty($settings[$startSyncDateKey])) {
            throw new \LogicException('Start sync date can\'t be empty');
        }

        if (!($settings[$startSyncDateKey] instanceof \DateTime)) {
            $settings[$startSyncDateKey] = new \DateTime($settings[$startSyncDateKey]);
        }

        $startSyncFrom = $settings[$startSyncDateKey];
        /** @var Channel $channel */
        $channel = $this->channelRepository
            ->getOrLoadById($context->getOption('channel'));
        $status  = $channel->getStatusesForConnector($this->getType(), Status::STATUS_COMPLETED)->first();
        if (false !== $status) {
            /** @var Status $status */
            $startSyncFrom = $status->getDate();
        }

        $this->lastSyncDate = $startSyncFrom;

        if (empty($settings['sync_range'])) {
            $settings['sync_range'] = self::DEFAULT_SYNC_RANGE;
        }
        if ($settings['sync_range'] instanceof \DateInterval) {
            $this->syncRange = $settings['sync_range'];
        } else {
            $this->syncRange = \DateInterval::createFromDateString($settings['sync_range']);
        }

        if (!empty($settings['batch_size'])) {
            $this->batchSize = $settings['batch_size'];
        }

        // init helper connector
        $this->storeConnector->setStepExecution($this->getStepExecution());
    }

    /**
     * {@inheritdoc}
     */
    public function doRead()
    {
        $this->preLoadDependencies();

        $result = $this->findEntitiesToProcess();
        // no more data to look for
        if (is_null($result)) {
            return null;
        }

        // keep going till endDate >= NOW
        if (!empty($this->entitiesIdsBuffer)) {
            $entityId = array_shift($this->entitiesIdsBuffer);

            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            $this->logger->info(sprintf('[%s] loading entity ID: %d', $now->format('d-m-Y H:i:s'), $entityId));

            $data = $this->getData($entityId, true);
        } else {
            // empty record, nothing found but keep going
            $data = false;
        }

        return $data;
    }

    /**
     * Pre-load dependencies
     * Load method will be called only once
     */
    protected function preLoadDependencies()
    {
        if (!empty($this->dependencies)) {
            return;
        }

        $this->loadDependencies();
    }

    /**
     * @param int       $websiteId
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param string    $format
     *
     * @return array
     */
    protected function getBatchFilter($websiteId, \DateTime $startDate, \DateTime $endDate, $format = 'Y-m-d H:i:s')
    {
        return [
            'complex_filter' => [
                [
                    'key'   => 'updated_at',
                    'value' => [
                        'key'   => 'from',
                        'value' => $startDate->format($format),
                    ],
                ],
                [
                    'key'   => 'updated_at',
                    'value' => [
                        'key'   => 'to',
                        'value' => $endDate->format($format),
                    ],
                ],
                [
                    'key'   => 'website_id',
                    'value' => ['key' => 'eq', 'value' => $websiteId]
                ]
            ],
        ];
    }


    /**
     * Load entities ids list
     *
     * @return bool|null
     */
    protected function findEntitiesToProcess()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        if (!empty($this->entitiesIdsBuffer)) {
            return false;
        }

        $startDate = $this->lastSyncDate;
        $endDate   = clone $this->lastSyncDate;
        $endDate   = $endDate->add($this->syncRange);

        if ($startDate >= $now) {
            return null;
        }

        $this->logger->info(
            sprintf(
                '[%s] Looking for entities from %s to %s ... ',
                $now->format('d-m-Y H:i:s'),
                $startDate->format('d-m-Y H:i:s'),
                $endDate->format('d-m-Y H:i:s')
            )
        );

        $filters                 = [
            $this->getBatchFilter(
                $this->transportSettings->get('website_id'),
                $startDate,
                $endDate
            )
        ];
        $this->entitiesIdsBuffer = $this->getList($filters, $this->batchSize, true);

        $this->logger->info(sprintf('found %d entities', count($this->entitiesIdsBuffer)));
        $this->lastSyncDate = $endDate;

        // no more data to look for
        if (empty($this->entitiesIdsBuffer) && $endDate >= $now) {
            $result = null;
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * @param $websiteId
     *
     * @throws \LogicException
     */
    protected function getStoreByWebsiteId($websiteId)
    {
        $store = array_filter(
            $this->dependencies[self::ALIAS_STORES],
            function ($store) use ($websiteId) {
                return $store['website_id'] == $websiteId;
            }
        );
        $store = reset($store);

        if ($store === false) {
            throw new \LogicException(sprintf('Could not resolve store dependency for website id: %d', $websiteId));
        }
    }

    /**
     * Get entities list
     *
     * @param array $filters
     * @param null  $limit
     * @param bool  $idsOnly
     *
     * @return mixed
     */
    abstract public function getList($filters = [], $limit = null, $idsOnly = true);

    /**
     * Get entity data by id
     *
     * @param int        $id
     * @param bool       $dependenciesInclude
     * @param array|null $onlyAttributes array of needed attributes or null to get all list
     *
     * @return mixed
     */
    abstract public function getData($id, $dependenciesInclude = false, $onlyAttributes = null);

    /**
     * Do real load for dependencies data
     *
     * @return void
     */
    abstract protected function loadDependencies();

    /**
     * Should return connector type string
     *
     * @return string
     */
    abstract protected function getType();
}
