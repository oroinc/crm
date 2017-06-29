<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\MagentoBundle\Provider\BatchFilterBag;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class OrderBridgeIterator extends AbstractBridgeIterator
{
    /**
     * {@inheritdoc}
     */
    protected function applyFilter()
    {
        if ($this->websiteId !== Website::ALL_WEBSITES) {
            $this->filter->addStoreFilter($this->getStoresByWebsiteId($this->websiteId));
        }
        parent::applyFilter();
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityIds()
    {
        $this->applyFilter();

        $filters          = $this->filter->getAppliedFilters();
        $filters['pager'] = ['page' => $this->getCurrentPage(), 'pageSize' => $this->pageSize];

        $this->loadByFilters($filters);

        return array_keys($this->entityBuffer);
    }

    /**
     * {@inheritdoc}
     */
    protected function loadEntities(array $ids)
    {
        if (!$ids) {
            return;
        }

        $filters = new BatchFilterBag();
        $filters->addComplexFilter(
            'in',
            [
                'key' => $this->getIdFieldName(),
                'value' => [
                    'key' => 'in',
                    'value' => implode(',', $ids)
                ]
            ]
        );

        if (null !== $this->websiteId && $this->websiteId !== Website::ALL_WEBSITES) {
            $filters->addWebsiteFilter([$this->websiteId]);
        }

        $filters = $filters->getAppliedFilters();
        $filters['pager'] = ['page' => $this->getCurrentPage(), 'pageSize' => $this->pageSize];

        $this->loadByFilters($filters);
    }

    /**
     * @param array $filters
     */
    protected function loadByFilters(array $filters)
    {
        $result = $this->transport->call(SoapTransport::ACTION_ORO_ORDER_LIST, $filters);
        $result = $this->processCollectionResponse($result);

        $resultIds = array_map(
            function (&$item) {
                $item->items = $this->processCollectionResponse($item->items);

                return $item->order_id;
            },
            $result
        );
        $this->entityBuffer = array_combine($resultIds, $result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdFieldName()
    {
        return 'order_id';
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $this->logger->info(sprintf('Loading Order by id: %s', $this->key()));

        return $this->current;
    }
}
