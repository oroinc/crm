<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class OrderBridgeIterator extends AbstractBridgeIterator
{
    /**
     * {@inheritdoc}
     */
    protected function getEntityIds()
    {
        $this->filter
            ->addStoreFilter($this->getStoresByWebsiteId($this->websiteId))
            ->addDateFilter($this->mode == self::IMPORT_MODE_INITIAL, $this->lastSyncDate);

        $result = $this->transport->call(
            SoapTransport::ACTION_ORO_ORDER_LIST,
            [$this->filter->getAppliedFilters()],
            ['page' => $this->getCurrentPage(), 'pageSize' => self::DEFAULT_PAGE_SIZE]
        );

        $resultIds = array_map(
            function ($item) {
                return (object)['increment_id' => $item->increment_id, 'entity_id' => $item->order_id];
            },
            $result
        );
        $this->entityBuffer = array_combine($resultIds, $result);

        return $result;
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
