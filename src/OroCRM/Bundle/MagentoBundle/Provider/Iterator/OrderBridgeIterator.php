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
        $this->filter->addStoreFilter($this->getStoresByWebsiteId($this->websiteId));
        parent::applyFilter();
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityIds()
    {
        $this->applyFilter();

        $filters          = $this->filter->getAppliedFilters();
        $filters['pager'] = ['page' => $this->getCurrentPage(), 'pageSize' => self::DEFAULT_PAGE_SIZE];

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
    protected function getDependencies()
    {
        return [
            self::ALIAS_STORES   => iterator_to_array($this->transport->getStores()),
            self::ALIAS_WEBSITES => iterator_to_array($this->transport->getWebsites())
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdFieldName()
    {
        return 'order_id';
    }
}
