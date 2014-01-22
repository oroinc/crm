<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class CustomerBridgeIterator extends AbstractBridgeIterator
{
    /**
     * {@inheritdoc}
     */
    protected function applyFilter()
    {
        $this->filter->addWebsiteFilter([$this->websiteId]);
        if ($this->mode == self::IMPORT_MODE_UPDATE) {
            $this->filter->addDateFilter(false, $this->lastSyncDate);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityIds()
    {
        $this->applyFilter();

        $result = $this->transport->call(
            SoapTransport::ACTION_ORO_CUSTOMER_LIST,
            [
                $this->filter->getAppliedFilters(),
                ['page' => $this->getCurrentPage(), 'pageSize' => self::DEFAULT_PAGE_SIZE]
            ]
        );

        $resultIds = array_map(
            function ($item) {
                return $item->customer_id;
            },
            $result
        );
        $this->entityBuffer = array_combine($resultIds, $result);

        return $resultIds;
    }

    protected function addDependencyData($result)
    {
        // TODO: implement convertion using customer data converter
        //return parent::addDependencyData($result);

        // TODO: remove this after TODO implementation
        $result->group               = $this->dependencies[self::ALIAS_GROUPS][$result->group_id];
        $result->group['originId']   = $result->group['customer_group_id'];
        $result->store               = $this->dependencies[self::ALIAS_STORES][$result->store_id];
        $result->store['originId']   = $result->store_id;
        $result->website             = $this->dependencies[self::ALIAS_WEBSITES][$result->website_id];
        $result->website['originId'] = $result->website['id'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getDependencies()
    {
        return [
            self::ALIAS_STORES   => iterator_to_array($this->transport->getStores()),
            self::ALIAS_WEBSITES => iterator_to_array($this->transport->getWebsites()),
            self::ALIAS_GROUPS   => iterator_to_array($this->transport->getCustomerGroups()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdFieldName()
    {
        return 'customer_id';
    }
}
