<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;

use OroCRM\Bundle\MagentoBundle\Provider\BatchFilterBag;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\ServerTimeAwareInterface;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

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
    public function __construct(SoapTransport $transport, array $settings)
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
            $this->filter->addDateFilter($dateField, 'from', $this->getToDate($this->lastSyncDate));
            $this->filter->addDateFilter($dateField, 'to', $this->lastSyncDate);
        } else {
            $dateField = 'updated_at';
            $this->filter->addDateFilter($dateField, 'gt', $this->lastSyncDate);
        }

        $this->fixServerTime($dateField);

        if (null !== $this->predefinedFilters) {
            $this->filter->merge($this->predefinedFilters);
        }

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

    /**
     * Fix time frame if it's possible to retrieve server time.
     *
     * @param string $dateField
     */
    protected function fixServerTime($dateField)
    {
        if (!$this->isInitialSync() && $this->transport instanceof ServerTimeAwareInterface) {
            $time = $this->transport->getServerTime();
            if (false !== $time) {
                $frameLimit = new \DateTime($time, new \DateTimeZone('UTC'));
                $this->filter->addDateFilter($dateField, 'lte', $frameLimit);

                return $frameLimit;
            }
        }

        return false;
    }
}
