<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;
use OroCRM\Bundle\MagentoBundle\Provider\BatchFilterBag;

abstract class AbstractBridgeIterator extends AbstractPageableSoapIterator implements PredefinedFiltersAwareInterface
{
    const DEFAULT_PAGE_SIZE = 100;

    /** @var int */
    protected $currentPage = 1;

    /** @var \stdClass[] Entities buffer got from pageable remote */
    protected $entityBuffer = null;

    /** @var bool */
    protected $lastPageAssumed = false;

    /** @var BatchFilterBag */
    protected $predefinedFilters;

    /**
     * {@inheritdoc}
     */
    public function setPredefinedFiltersBag(BatchFilterBag $bag)
    {
        $this->predefinedFilters = $bag;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFilter()
    {
        if ($this->mode == self::IMPORT_MODE_INITIAL) {
            $dateField = 'created_at';
        } else {
            $dateField = 'updated_at';
        }

        $this->filter->addDateFilter($dateField, 'from', $this->lastSyncDate);

        if (null !== $this->predefinedFilters) {
            $this->filter->merge($this->predefinedFilters);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntitiesToProcess()
    {
        if ($this->lastPageAssumed) {
            return null;
        }

        $this->logger->info('Looking for batch');
        $this->entitiesIdsBuffer = $this->getEntityIds();
        $this->currentPage++;

        $this->logger->info(sprintf('found %d entities', count($this->entitiesIdsBuffer)));

        // if previous result batch items count less then requested page size
        // then assume that it's last page
        if (count($this->entityBuffer) < self::DEFAULT_PAGE_SIZE) {
            $this->lastPageAssumed = true;
        }

        return empty($this->entitiesIdsBuffer) ? null : true;
    }

    /**
     * @return int
     */
    protected function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntity($id)
    {
        $result = $this->entityBuffer[$id];

        $this->addDependencyData($result);

        return ConverterUtils::objectToArray($result);
    }
}
