<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator;

use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\MagentoBundle\Provider\BatchFilterBag;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
abstract class AbstractPageableIterator implements \Iterator, UpdatedLoaderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    const DEFAULT_SYNC_RANGE = '1 month';

    /** @var \DateTime needed to restore initial value on next rewinds */
    protected $lastSyncDateInitialValue;

    /** @var \DateTime */
    protected $lastSyncDate;

    /** @var \DateTime */
    protected $minSyncDate;

    /** @var string initial or update mode */
    protected $mode = self::IMPORT_MODE_UPDATE;

    /** @var \DateInterval */
    protected $syncRange;

    /** @var MagentoTransportInterface */
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

    /** @var \stdClass[] Entities buffer got from pageable remote */
    protected $entityBuffer;

    /** @var bool */
    protected $isInitialDataLoaded = false;

    /** @var array */
    protected $storesByWebsite = [];

    /**
     * @param MagentoTransportInterface $transport
     * @param array $settings
     */
    public function __construct(MagentoTransportInterface $transport, array $settings)
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
        if (!$this->entitiesIdsBufferImmutable) {
            $this->entitiesIdsBuffer = [];
        }
        $this->current = null;
        $this->lastSyncDate = clone $this->lastSyncDateInitialValue;
        $this->filter->reset();
        $this->next();
    }

    /**
     * {@inheritdoc}
     */
    public function setStartDate(\DateTime $date)
    {
        $this->lastSyncDate = clone $date;
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

        // drop missing customer ids
        $this->entitiesIdsBuffer = array_intersect(array_keys((array)$this->entityBuffer), $entitiesIdsBuffer);
    }

    /**
     * @TODO refactor class to remove empty methods. According to CRM-8211
     * @param array $ids
     */
    protected function loadEntities(array $ids)
    {
    }

    /**
     * @param \DateTime $date
     * @param array $websiteIds
     * @param array $storeIds
     * @param string $format
     *
     * @return array
     */
    protected function getBatchFilter(
        \DateTime $date,
        array $websiteIds = [],
        array $storeIds = [],
        $format = 'Y-m-d H:i:s'
    ) {
        $this->applyWebsiteFilters($websiteIds, $storeIds);

        if ($this->isInitialSync()) {
            $this->filter->addDateFilter('created_at', 'from', $this->getToDateInitial($date), $format);
            $this->filter->addDateFilter('created_at', 'to', $date, $format);
        } else {
            $this->filter->addDateFilter('updated_at', 'from', $date);
            $this->filter->addDateFilter('updated_at', 'to', $date->add($this->syncRange));
        }

        $this->modifyFilters();
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
        if ($this->isInitialSync() && $this->isInitialDataLoaded) {
            $this->isInitialDataLoaded = false;

            return null;
        }

        $now = new \DateTime($this->transport->getServerTime(), new \DateTimeZone('UTC'));

        $this->logger->info('Looking for batch');
        $this->entitiesIdsBuffer = $this->getEntityIds();

        $this->logger->info(sprintf('found %d entities', count($this->entitiesIdsBuffer)));

        if ($this->isInitialSync()) {
            $this->isInitialDataLoaded = true;
        } elseif (empty($this->entitiesIdsBuffer)) {
            if ($this->lastSyncDate >= $now) {
                return null;
            }

            return true;
        }

        return empty($this->entitiesIdsBuffer) ? null : true;
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
        if (empty($this->storesByWebsite)) {
            $this->storesByWebsite[$websiteId] = [];
            $storesList = $this->transport->getStores();
            foreach ($storesList as $store) {
                $this->storesByWebsite[$store['website_id']][] = $store['store_id'];
            }
        }

        $stores = $this->storesByWebsite[$websiteId];
        if (empty($this->storesByWebsite[$websiteId])) {
            throw new \LogicException(sprintf('Could not resolve store dependency for website id: %d', $websiteId));
        }

        return $stores;
    }

    /**
     * Add debug records about applied filters to logger
     *
     * @param BatchFilterBag $filterBag
     */
    protected function logAppliedFilters(BatchFilterBag $filterBag)
    {
        $filters = $filterBag->getAppliedFilters();
        $filters = $filters['filters'];
        $filters = array_merge(
            !empty($filters[BatchFilterBag::FILTER_TYPE_COMPLEX]) ? $filters[BatchFilterBag::FILTER_TYPE_COMPLEX] : [],
            !empty($filters[BatchFilterBag::FILTER_TYPE_SIMPLE]) ? $filters[BatchFilterBag::FILTER_TYPE_SIMPLE] : []
        );
        $template = 'Filter applied: %s %s';
        foreach ($filters as $filter) {
            $field = $filter['key'];
            $value = $filter['value'];
            if (is_array($value)) {
                $value = http_build_query($value, '', '; ');
            } else {
                $value = '= ' . $value;
            }

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
    protected function getToDateInitial(\DateTime $date)
    {
        $toDate = clone $date;
        $toDate->sub($this->syncRange);
        if ($this->minSyncDate && $toDate < $this->minSyncDate) {
            $toDate = $this->minSyncDate;
        }

        return $toDate;
    }

    /**
     * @param \DateTime $dateToSync
     *
     * @return \DateTime
     */
    protected function getToDate(\DateTime $dateToSync)
    {
        $dateTo = clone $dateToSync;
        $dateTo->add($this->syncRange);
        $time = $this->transport->getServerTime();
        if (false !== $time) {
            $frameLimit = new \DateTime($time, new \DateTimeZone('UTC'));
            if ($frameLimit < $dateTo) {
                return $frameLimit;
            }
        }

        return $dateTo;
    }

    /**
     * @return bool
     */
    protected function isInitialSync()
    {
        return $this->mode === self::IMPORT_MODE_INITIAL;
    }

    /**
     * @param array $websiteIds
     * @param array $storeIds
     */
    protected function applyWebsiteFilters(array $websiteIds, array $storeIds)
    {
        if ($this->websiteId !== Website::ALL_WEBSITES) {
            if (!empty($websiteIds)) {
                $this->filter->addWebsiteFilter($websiteIds);
            }

            if (!empty($storeIds)) {
                $this->filter->addStoreFilter($storeIds);
            }
        }
    }

    /**
     * @TODO refactor class to remove empty methods. According to CRM-8211
     * Modify filters before applying.
     */
    protected function modifyFilters()
    {
    }
}
