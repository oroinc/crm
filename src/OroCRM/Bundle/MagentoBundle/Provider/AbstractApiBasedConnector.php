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
    const IMPORT_MODE_INITIAL = 'initial';
    const IMPORT_MODE_UPDATE  = 'update';

    /** @var \DateTime */
    protected $lastSyncDate;

    /**
     * @var string|\stdClass
     * Last id used for initial import, paginating by created_at assuming that ids always incremented
     */
    protected $lastId = null;

    /** @var string initial or update mode */
    protected $mode;

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
        EntityManager $em = null,
        StoreConnector $storeConnector = null
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
        parent::initializeFromContext($context);

        // initialize deps
        $this->dependencies      = [];
        $this->entitiesIdsBuffer = [];
        $settings                = $this->transportSettings->all();

        // validate date boundary
        $startSyncDateKey = 'start_sync_date';
        if (empty($settings[$startSyncDateKey])) {
            throw new \LogicException('Start sync date can\'t be empty');
        }

        if (!($settings[$startSyncDateKey] instanceof \DateTime)) {
            $settings[$startSyncDateKey] = new \DateTime($settings[$startSyncDateKey]);
        }
        $this->lastSyncDate = clone $settings[$startSyncDateKey];

        /** @var Channel $channel */
        $channel = $this->channelRepository->getOrLoadById($context->getOption('channel'));

        // set start date and mode depending on status
        $status  = $channel->getStatusesForConnector($this->getType(), Status::STATUS_COMPLETED)->first();
        if (false !== $status) {
            /** @var Status $status */
            $this->lastSyncDate = clone $status->getDate();
            $this->mode = self::IMPORT_MODE_UPDATE;
        } else {
            $this->mode = self::IMPORT_MODE_INITIAL;
        }

        // validate range
        if (empty($settings['sync_range'])) {
            $settings['sync_range'] = self::DEFAULT_SYNC_RANGE;
        }
        if ($settings['sync_range'] instanceof \DateInterval) {
            $this->syncRange = $settings['sync_range'];
        } else {
            $this->syncRange = \DateInterval::createFromDateString($settings['sync_range']);
        }

        // set batch size
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

        if (!empty($this->entitiesIdsBuffer)) {
            $entityId = array_shift($this->entitiesIdsBuffer);
            $id = is_object($entityId) && !empty($entityId->entity_id) ? $entityId->entity_id : $entityId;

            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            $this->logger->info(sprintf('[%s] loading entity ID: %d', $now->format('d-m-Y H:i:s'), $id));

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
     * @param int|array $websiteId
     * @param \DateTime $endDate
     * @param string    $format
     *
     * @return array
     */
    protected function getBatchFilter($websiteId, \DateTime $endDate, $format = 'Y-m-d H:i:s')
    {
        if (!empty($websiteId['value'])) {
            $operator   = 'in';
            $storeValue = is_array($websiteId['value']) ? implode(',', $websiteId['value']) : $websiteId['value'];
            $storeField = $websiteId['field'];
        } else {
            $operator   = 'eq';
            $storeValue = $websiteId;
            $storeField = 'website_id';
        }

        $initMode = $this->mode == self::IMPORT_MODE_INITIAL;
        if ($initMode) {
            $dateField = 'created_at';
            $dateKey = 'to';
        } else {
            $dateField = 'updated_at';
            $dateKey = 'from';
        }

        $filter = [
            'complex_filter' => [
                [
                    'key'   => $dateField,
                    'value' => [
                        'key'   => $dateKey,
                        'value' => $endDate->format($format),
                    ],
                ],
                [
                    'key'   => $storeField,
                    'value' => [
                        'key'   => $operator,
                        'value' => $storeValue
                    ]
                ]
            ],
        ];

        $lastId = $this->getLastId();
        if (!is_null($lastId) && $initMode) {
            $filter['complex_filter'][] = [
                'key'   => $this->getIdFieldName(),
                'value' => [
                    'key'   => 'gt',
                    'value' => $lastId,
                ],
            ];
        }

        return $filter;
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

        $initMode = $this->mode == self::IMPORT_MODE_INITIAL;
        if ($initMode) {
            $lastId      = $this->getLastId();
            $dateMessage = 'created less';
            $message     = sprintf(' and ID > %s', $lastId);
        } else {
            $dateMessage = 'updated more';
            $message     = '';
        }

        $this->logger->info(
            sprintf(
                '[%s] Looking for entities %s then %s%s ... ',
                $now->format('d-m-Y H:i:s'),
                $dateMessage,
                $this->lastSyncDate->format('d-m-Y H:i:s'),
                $message
            )
        );

        $filters = [
            $this->getBatchFilter(
                $this->transportSettings->get('website_id'),
                $this->lastSyncDate
            )
        ];
        $this->entitiesIdsBuffer = $this->getList($filters, $this->batchSize, true);

        // first run, ignore all data in less then start sync date
        $lastId       = $this->getLastId();
        $wasNull      = is_null($lastId);
        $lastId       = end($this->entitiesIdsBuffer);
        $this->lastId = $lastId ? $lastId : 0;

        // restore cursor
        reset($this->entitiesIdsBuffer);

        if ($wasNull && $initMode) {
            $this->entitiesIdsBuffer = [];
        } else {
            $this->logger->info(sprintf('found %d entities', count($this->entitiesIdsBuffer)));
        }

        // no more data to look for
        if ($initMode) {
            $lastSyncDate = $this->lastSyncDate;
        } else {
            $lastSyncDate = clone $this->lastSyncDate;
            $lastSyncDate->add($this->syncRange);
        }

        if (empty($this->entitiesIdsBuffer) && $lastSyncDate >= $now) {
            $result = null;
        } else {
            $result = true;
        }

        $this->lastSyncDate->add($this->syncRange);

        return $result;
    }

    /**
     * @param $websiteId
     *
     * @return array
     * @throws \LogicException
     */
    protected function getStoresByWebsiteId($websiteId)
    {
        $stores = array_filter(
            $this->dependencies[self::ALIAS_STORES],
            function ($store) use ($websiteId) {
                return $store['website_id'] == $websiteId;
            }
        );

        if ($stores === false) {
            throw new \LogicException(sprintf('Could not resolve store dependency for website id: %d', $websiteId));
        }

        $stores = array_map(
            function ($item) {
                return $item['store_id'];
            },
            $stores
        );

        return $stores;
    }

    /**
     * Retrieve last id in queue
     *
     * @return int|null
     */
    protected function getLastId()
    {
        return is_object($this->lastId) ? $this->lastId->entity_id : $this->lastId;
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

    /**
     * Should return id field name for entity, e.g.: entity_id, order_id, increment_id, etc
     *
     * @return string
     */
    abstract protected function getIdFieldName();
}
