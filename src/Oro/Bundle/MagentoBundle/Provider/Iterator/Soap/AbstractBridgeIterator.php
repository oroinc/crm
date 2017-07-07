<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;
use Oro\Bundle\MagentoBundle\Provider\BatchFilterBag;
use Oro\Bundle\MagentoBundle\Provider\Iterator\PredefinedFiltersAwareInterface;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoSoapTransportInterface;

abstract class AbstractBridgeIterator extends AbstractPageableSoapIterator implements PredefinedFiltersAwareInterface
{
    const DEFAULT_PAGE_SIZE = 100;

    /** @var int */
    protected $currentPage = 1;

    /** @var bool */
    protected $lastPageAssumed = false;

    /** @var BatchFilterBag */
    protected $predefinedFilters;

    /** @var int */
    protected $pageSize;

    /**
     * {@inheritdoc}
     */
    public function __construct(MagentoSoapTransportInterface $transport, array $settings)
    {
        parent::__construct($transport, $settings);

        $this->pageSize = !empty($settings['page_size']) ? (int)$settings['page_size'] : self::DEFAULT_PAGE_SIZE;
    }

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
        if ($this->isInitialSync()) {
            $dateField = 'created_at';
            $this->filter->addDateFilter($dateField, 'from', $this->getToDateInitial($this->lastSyncDate));
            $this->filter->addDateFilter($dateField, 'to', $this->lastSyncDate);
        } else {
            $dateField = 'updated_at';
            $this->filter->addDateFilter($dateField, 'from', $this->lastSyncDate);
            $this->filter->addDateFilter($dateField, 'to', $this->getToDate($this->lastSyncDate));
        }

        if (null !== $this->predefinedFilters) {
            $this->filter->merge($this->predefinedFilters);
        }

        $this->filter->resetFilterWithEmptyValue();

        $this->logAppliedFilters($this->filter);
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
        if (count($this->entityBuffer) < $this->pageSize) {
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
        if (!array_key_exists($id, $this->entityBuffer)) {
            $this->logger->warning(sprintf('Entity with id "%s" was not found', $id));

            return false;
        }

        $result = $this->entityBuffer[$id];

        return ConverterUtils::objectToArray($result);
    }
}
