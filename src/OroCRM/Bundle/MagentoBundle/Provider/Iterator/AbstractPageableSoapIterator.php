<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

abstract class AbstractPageableSoapIterator implements \Iterator, UpdatedLoaderInterface
{
    const ALIAS_GROUPS   = 'groups';
    const ALIAS_STORES   = 'stores';
    const ALIAS_WEBSITES = 'websites';
    const ALIAS_REGIONS  = 'regions';

    const DEFAULT_SYNC_RANGE = '1 month';

    /** @var \DateTime needed to restore initial value on next rewinds */
    protected $lastSyncDateInitialValue;

    /** @var \DateTime */
    protected $lastSyncDate;

    /** @var int|\stdClass Last id used for initial mode, paging by created_at assuming that ids always incremented */
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

    /** @var bool */
    protected $loaded = false;

    public function __construct(SoapTransport $transport, array $settings)
    {
        $this->transport = $transport;
        $this->websiteId = $settings['website_id'];

        // validate date boundary
        $startSyncDateKey = 'start_sync_date';
        if (empty($settings[$startSyncDateKey])) {
            throw new \LogicException('Start sync date can\'t be empty');
        }

        $this->setStartDate($settings[$startSyncDateKey]);

        $this->syncRange = \DateInterval::createFromDateString(self::DEFAULT_SYNC_RANGE);
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
        do {
            if (!empty($this->entitiesIdsBuffer)) {
                $entityId = array_shift($this->entitiesIdsBuffer);
                $result   = $this->getEntity($entityId);
            } else {
                $result = $this->findEntitiesToProcess();
            }

            // no more data to look for
            if (is_null($result)) {
                break;
            }

            // loop again if result is true
            // true means that there are entities to process or
            // there are intervals to retrieve entities there
        } while ($result === true);

        $this->current = $result;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return is_object($this->current)
            ? $this->current->{$this->getIdFieldName()}
            : $this->current[$this->getIdFieldName()];
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
            $this->dependencies = $this->getDependencies();
        }

        $this->entitiesIdsBuffer = [];
        $this->current           = null;
        $this->lastSyncDate      = clone $this->lastSyncDateInitialValue;
        $this->next();
    }

    /**
     * {@inheritdoc}
     */
    public function setStartDate(\DateTime $date)
    {
        $this->lastSyncDate             = clone $date;
        $this->lastSyncDateInitialValue = clone $date;
    }

    /**
     * {@inheritdoc}
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @param \DateTime $date
     * @param array     $websiteIds
     * @param array     $storeIds
     * @param string    $format
     *
     * @return array
     */
    protected function getBatchFilter(
        \DateTime $date,
        array $websiteIds = [],
        array $storeIds = [],
        $format = 'Y-m-d H:i:s'
    ) {
        $initMode = $this->mode == self::IMPORT_MODE_INITIAL;
        $filters  = ['complex_filter' => []];

        if (!empty($websiteIds)) {
            $filters['complex_filter'][] = [
                'key'   => 'website_id',
                'value' => [
                    'key'   => 'in',
                    'value' => implode(',', $websiteIds)
                ]
            ];
        }
        if (!empty($storeIds)) {
            $filters['complex_filter'][] = [
                'key'   => 'store_id',
                'value' => [
                    'key'   => 'in',
                    'value' => implode(',', $storeIds)
                ]
            ];
        }

        if ($initMode) {
            $dateField = 'created_at';
            $dateKey   = 'to';
        } else {
            $dateField = 'updated_at';
            $dateKey   = 'from';
        }

        $filters['complex_filter'][] = [
            'key'   => $dateField,
            'value' => [
                'key'   => $dateKey,
                'value' => $date->format($format),
            ],
        ];

        $lastId = $this->getLastId();
        if (!is_null($lastId) && $initMode) {
            $filters['complex_filter'][] = [
                'key'   => $this->getIdFieldName(),
                'value' => [
                    'key'   => 'gt',
                    'value' => $this->getLastId()
                ],
            ];
        }

        return $filters;
    }

    /**
     * Load entities ids list
     *
     * @return true|null true when there are ids retrieved
     */
    protected function findEntitiesToProcess()
    {
        $now      = new \DateTime('now', new \DateTimeZone('UTC'));
        $initMode = $this->mode == self::IMPORT_MODE_INITIAL;

        $this->entitiesIdsBuffer = $this->getEntityIds();

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

        // if init mode and it's first iteration we have to skip retrieved entities
        if ($wasNull && $initMode) {
            $this->entitiesIdsBuffer = [];
        }

        if ($initMode) {
            $lastSyncDate = $this->lastSyncDate;
        } else {
            $lastSyncDate = clone $this->lastSyncDate;
            $lastSyncDate->add($this->syncRange);
        }

        //increment date for further filtering
        $this->lastSyncDate->add($this->syncRange);

        return empty($this->entitiesIdsBuffer) && $lastSyncDate >= $now ? null : true;
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
     * Retrieve store ids for given website
     *
     * @param $websiteId
     *
     * @return array
     * @throws \LogicException
     */
    protected function getStoresByWebsiteId($websiteId)
    {
        $stores = [];

        foreach ((array)$this->dependencies[self::ALIAS_STORES] as $store) {
            if ($store['website_id'] == $websiteId) {
                $stores[] = $store['store_id'];
            }
        }

        if (empty($stores)) {
            throw new \LogicException(sprintf('Could not resolve store dependency for website id: %d', $websiteId));
        }

        return $stores;
    }

    /**
     * Get dependencies data
     *
     * @return array
     */
    protected function getDependencies()
    {
        return [];
    }

    /**
     * Get entities ids list
     *
     * @return mixed
     */
    abstract protected function getEntityIds();

    /**
     * Get entity data by id
     *
     * @param mixed $id
     *
     * @return mixed
     */
    abstract protected function getEntity($id);

    /**
     * Should return id field name for entity, e.g.: entity_id, order_id, increment_id, etc
     * Needed for complex filters for API calls
     *
     * @return string
     */
    abstract protected function getIdFieldName();
}
