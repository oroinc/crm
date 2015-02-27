<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use Psr\Log\NullLogger;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

use OroCRM\Bundle\MagentoBundle\Utils\WSIUtils;
use OroCRM\Bundle\MagentoBundle\Provider\BatchFilterBag;
use OroCRM\Bundle\MagentoBundle\Provider\Dependency\AbstractDependencyManager;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
abstract class AbstractPageableSoapIterator implements \Iterator, UpdatedLoaderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    const DEFAULT_SYNC_RANGE = '1 month';

    /** @var \DateTime needed to restore initial value on next rewinds */
    protected $lastSyncDateInitialValue;

    /** @var \DateTime */
    protected $lastSyncDate;

    /** @var \DateTime */
    protected $minSyncDate;

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

    /** @var array */
    protected $entitiesIdsBuffer = [];

    /**
     * In case entities ids were predefined we should not load next iteration
     *
     * @var bool
     */
    protected $entitiesIdsBufferImmutable = false;

    /** @var null|\stdClass */
    protected $current;

    /** @var bool */
    protected $loaded = false;

    /** @var \stdClass[] Entities buffer got from pageable remote */
    protected $entityBuffer;

    /**
     * @param SoapTransport $transport
     * @param array $settings
     */
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
            $result = true;

            if (!empty($this->entitiesIdsBuffer)) {
                $entityId = array_shift($this->entitiesIdsBuffer);
                $result = $this->getEntity($entityId);
                if ($result === false) {
                    continue;
                }
            } elseif ($this->entitiesIdsBufferImmutable && empty($this->entitiesIdsBuffer)) {
                $result = null;
            } elseif (!$this->entitiesIdsBufferImmutable) {
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
        if (is_object($this->current)) {
            return $this->current->{$this->getIdFieldName()};
        } else {
            return $this->current[$this->getIdFieldName()];
        }
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
            // Reload loaded dependencies for deltas
            if (!$this->isInitialSync()) {
                $this->transport->getDependencies(null, true);
            }
            $this->loaded = true;
        }

        if (!$this->entitiesIdsBufferImmutable) {
            $this->entitiesIdsBuffer = [];
        }
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
    public function getStartDate()
    {
        return $this->lastSyncDateInitialValue;
    }

    /**
     * {@inheritdoc}
     */
    public function setMinSyncDate(\DateTime $date)
    {
        $this->minSyncDate = $date;
    }

    /**
     * {@inheritdoc}
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @param \DateInterval $syncRange
     */
    public function setSyncRange(\DateInterval $syncRange)
    {
        $this->syncRange = $syncRange;
    }

    /**
     * @param array $entitiesIdsBuffer
     */
    public function setEntitiesIdsBuffer(array $entitiesIdsBuffer)
    {
        $this->entitiesIdsBuffer = $entitiesIdsBuffer;
        $this->entitiesIdsBufferImmutable = true;

        $this->loadEntities($entitiesIdsBuffer);
    }

    /**
     * @param array $ids
     */
    protected function loadEntities(array $ids)
    {
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
        if ($this->websiteId !== StoresSoapIterator::ALL_WEBSITES) {
            if (!empty($websiteIds)) {
                $this->filter->addWebsiteFilter($websiteIds);
            }

            if (!empty($storeIds)) {
                $this->filter->addStoreFilter($storeIds);
            }
        }

        $toDate = $this->getToDate($date);
        $dateField = 'updated_at';
        $initMode = $this->isInitialSync();
        if ($initMode) {
            $dateField = 'created_at';
        }
        $this->filter->addDateFilter($dateField, 'to', $date, $format);
        $this->filter->addDateFilter($dateField, 'from', $toDate, $format);

        $lastId = $this->getLastId();
        if (!is_null($lastId) && $initMode) {
            $this->filter->addLastIdFilter($lastId, $this->getIdFieldName());
        }

        $this->logAppliedFilters($this->filter);

        return $this->filter->getAppliedFilters();
    }

    /**
     * Load entities ids list
     *
     * @return true|null true when there are ids retrieved
     */
    protected function findEntitiesToProcess()
    {
        $now      = new \DateTime($this->transport->getServerTime(), new \DateTimeZone('UTC'));
        $initMode = $this->isInitialSync();

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
//            @todo: restore after requirements clarifying
//            $this->entitiesIdsBuffer = [];
//            $this->logger->info('Pagination start point detected');
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

        $dependencies = $this->transport->getDependencies([MagentoTransportInterface::ALIAS_STORES]);
        foreach ($dependencies[MagentoTransportInterface::ALIAS_STORES] as $store) {
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
        AbstractDependencyManager::addDependencyData($result, $this->transport);
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
     * Add debug records about applied filters to logger
     *
     * @param BatchFilterBag $filterBag
     */
    protected function logAppliedFilters(BatchFilterBag $filterBag)
    {
        $filters  = $filterBag->getAppliedFilters();
        $filters  = $filters['filters'];
        $filters  = array_merge(
            !empty($filters[BatchFilterBag::FILTER_TYPE_COMPLEX]) ? $filters[BatchFilterBag::FILTER_TYPE_COMPLEX] : [],
            !empty($filters[BatchFilterBag::FILTER_TYPE_SIMPLE]) ? $filters[BatchFilterBag::FILTER_TYPE_SIMPLE] : []
        );
        $template = 'Filter applied: %s %s';
        foreach ($filters as $filter) {
            $field = $filter['key'];
            $value = $filter['value'];
            $value = is_array($value) ? http_build_query($value, '', '; ') : '= ' . $value;

            $this->logger->debug(sprintf($template, $field, urldecode($value)));
        }
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

    /**
     * @param \DateTime $date
     * @return \DateTime
     */
    protected function getToDate(\DateTime $date)
    {
        $toDate = clone $date;
        $toDate->sub($this->syncRange);
        if ($this->minSyncDate && $toDate < $this->minSyncDate) {
            $toDate = $this->minSyncDate;
        }

        return $toDate;
    }

    /**
     * @return bool
     */
    protected function isInitialSync()
    {
        return $this->mode === self::IMPORT_MODE_INITIAL;
    }
}
