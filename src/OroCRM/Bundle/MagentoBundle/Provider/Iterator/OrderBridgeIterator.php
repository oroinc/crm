<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class OrderBridgeIterator extends AbstractBridgeIterator
{
    /**
     * {@inheritdoc}
     */
    protected function applyFilter()
    {
        if ($this->websiteId !== StoresSoapIterator::ALL_WEBSITES) {
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

        $result = $this->transport->call(SoapTransport::ACTION_ORO_ORDER_LIST, $filters);
        $result = $this->processCollectionResponse($result);

        $that               = $this;
        $resultIds          = array_map(
            function (&$item) use ($that) {
                $item->items = $that->processCollectionResponse($item->items);

                return $item->order_id;
            },
            $result
        );
        $this->entityBuffer = array_combine($resultIds, $result);

        return $resultIds;
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
