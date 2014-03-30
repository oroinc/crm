<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use Psr\Log\NullLogger;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

use OroCRM\Bundle\MagentoBundle\Utils\WSIUtils;
use OroCRM\Bundle\MagentoBundle\Provider\BatchFilterBag;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

abstract class AbstractPageableSoapIterator implements \Iterator, UpdatedLoaderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

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

    /** @var BatchFilterBag */
    protected $filter;

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

        $this->setLogger(new NullLogger());
        $this->filter = new BatchFilterBag();

    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $this->logger->info(sprintf('Loading entity by id: %s', $this->key()));

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
            $this->loaded       = true;
        }

        $this->entitiesIdsBuffer = [];
        $this->current           = null;
        $this->lastSyncDate      = clone $this->lastSyncDateInitialValue;
        $this->filter->reset();
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
        if (!empty($websiteIds)) {
            $this->filter->addWebsiteFilter($websiteIds);
        }

        if (!empty($storeIds)) {
            $this->filter->addStoreFilter($storeIds);
        }

        $initMode = $this->mode == self::IMPORT_MODE_INITIAL;
        if ($initMode) {
            $dateField = 'created_at';
            $dateKey   = 'to';
        } else {
            $dateField = 'updated_at';
            $dateKey   = 'from';
        }
        $this->filter->addDateFilter($dateField, $dateKey, $date, $format);

        $lastId = $this->getLastId();
        if (!is_null($lastId) && $initMode) {
            $this->filter->addLastIdFilter($lastId, $this->getIdFieldName());
        }

        return $this->filter->getAppliedFilters();
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

        $this->logger->info('Looking for batch');
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
            $this->logger->info('Pagination start point detected');
        } else {
            $this->logger->info(sprintf('found %d entities', count($this->entitiesIdsBuffer)));
        }

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

        //increment date for further filtering
        $this->lastSyncDate->add($this->syncRange);

        return $result;
    }

    /**
     * Retrieve last id in queue
     *
     * @return int|null
     */
    protected function getLastId()
    {
        return is_object($this->lastId) ? $this->lastId->{$this->getIdFieldName()} : $this->lastId;
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
     * Adds dependencies to result entity
     *
     * @param \stdClass $result
     */
    protected function addDependencyData($result)
    {
        // fill related entities data, needed to create full representation of magento store state in this time
        // flat array structure will be converted by data converter
        $store   = $this->dependencies[self::ALIAS_STORES][$result->store_id];
        $website = $this->dependencies[self::ALIAS_WEBSITES][$store['website_id']];

        $result->store_code         = $store['code'];
        $result->store_storename    = $store['name'];
        $result->store_website_id   = $website['id'];
        $result->store_website_code = $website['code'];
        $result->store_website_name = $website['name'];
    }

    /**
     * @param mixed $response
     *
     * @return array
     */
    protected function processCollectionResponse($response)
    {
        return WSIUtils::processCollectionResponse($response);
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
