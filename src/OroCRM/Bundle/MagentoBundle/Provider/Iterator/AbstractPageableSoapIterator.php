<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

abstract class AbstractPageableSoapIterator implements \Iterator, UpdatedLoaderInterface
{
    const ALIAS_GROUPS   = 'groups';
    const ALIAS_STORES   = 'stores';
    const ALIAS_WEBSITES = 'websites';
    const ALIAS_REGIONS  = 'regions';

    const DEFAULT_SYNC_RANGE = '1 month';

    /** @var \DateTime */
    protected $lastSyncDate;

    /**
     * @var string|\stdClass
     * Last id used for initial import, paginating by created_at assuming that ids always incremented
     */
    protected $lastId = null;

    /** @var string initial or update mode */
    protected $mode = self::IMPORT_MODE_INITIAL;

    /** @var \DateInterval */
    protected $syncRange;

    /** @var SoapTransport */
    protected $transport;

    /** @var int */
    protected $websiteId;

    /** @var array dependencies data: customer groups, stores, websites */
    protected $dependencies = [];

    /** @var array */
    protected $entitiesIdsBuffer = [];

    /** @var null|\stdClass */
    protected $current;

    /** @var int */
    protected $currentKey;

    /** @var int */
    protected $batchSize;

    /** @var bool */
    protected $loaded = false;

    public function __construct(SoapTransport $transport, Channel $channel)
    {
        $this->transport = $transport;
        /** @var MagentoSoapTransport $transportEntity */
        $transportEntity = $channel->getTransport();
        $settings        = $transportEntity->getSettingsBag()->all();
        $this->websiteId = $transportEntity->getWebsiteId();

        // validate date boundary
        $startSyncDateKey = 'start_sync_date';
        if (empty($settings[$startSyncDateKey])) {
            throw new \LogicException('Start sync date can\'t be empty');
        }

        $this->setStartDate($settings[$startSyncDateKey]);

        $this->syncRange = \DateInterval::createFromDateString(self::DEFAULT_SYNC_RANGE);

        // set batch size
        if (!empty($settings['batch_size'])) {
            $this->batchSize = $settings['batch_size'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setStartDate(\DateTime $date)
    {
        $this->lastSyncDate = clone $date;
    }

    /**
     * {@inheritdoc}
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if (empty($this->entitiesIdsBuffer)) {
            $result = $this->findEntitiesToProcess();
        } else {
            $result = false;
        }

        // no more data to look for
        if (is_null($result)) {
            return null;
        }

        if (!empty($this->entitiesIdsBuffer)) {
            $entityId = array_shift($this->entitiesIdsBuffer);
            $data     = $this->getData($entityId, true);
        } else {
            // empty record, nothing found but keep going
            $data = false;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->currentKey;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return null !== $this->current;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        if (false === $this->loaded) {
            $this->preLoadDependencies();
        }

        $this->entitiesIdsBuffer = [];
        $this->next();
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
            $dateKey   = 'to';
        } else {
            $dateField = 'updated_at';
            $dateKey   = 'from';
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
        $now      = new \DateTime('now', new \DateTimeZone('UTC'));
        $initMode = $this->mode == self::IMPORT_MODE_INITIAL;

        $filters                 = [
            $this->getBatchFilter($this->websiteId, $this->lastSyncDate)
        ];
        $this->entitiesIdsBuffer = $this->getList($filters, $this->batchSize, true);

        // first run, ignore all data in less then start sync date
        $lastId  = $this->getLastId();
        $wasNull = is_null($lastId);
        $lastId  = end($this->entitiesIdsBuffer);

        if (!empty($lastId)) {
            $this->lastId = $lastId;
        } elseif ($wasNull) {
            $this->lastId = 0;
        }

        // restore cursor
        reset($this->entitiesIdsBuffer);

        if ($wasNull && $initMode) {
            $this->entitiesIdsBuffer = [];
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
     * Get entities list
     *
     * @param array $filters
     * @param null  $limit
     * @param bool  $idsOnly
     *
     * @return mixed
     */
    abstract protected function getList($filters = [], $limit = null, $idsOnly = true);

    /**
     * Get entity data by id
     *
     * @param int        $id
     * @param bool       $dependenciesInclude
     * @param array|null $onlyAttributes array of needed attributes or null to get all list
     *
     * @return mixed
     */
    abstract protected function getData($id, $dependenciesInclude = false, $onlyAttributes = null);

    /**
     * Should return id field name for entity, e.g.: entity_id, order_id, increment_id, etc
     *
     * @return string
     */
    abstract protected function getIdFieldName();

    /**
     * Do real load for dependencies data
     *
     * @return void
     */
    abstract protected function loadDependencies();
}
