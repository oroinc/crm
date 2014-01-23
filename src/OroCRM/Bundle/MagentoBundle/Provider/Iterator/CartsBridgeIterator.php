<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class CartsBridgeIterator extends AbstractBridgeIterator
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

        $result = $this->transport->call(
            SoapTransport::ACTION_ORO_CART_LIST,
            [
                $this->filter->getAppliedFilters(),
                ['page' => $this->getCurrentPage(), 'pageSize' => self::DEFAULT_PAGE_SIZE]
            ]
        );

        $resultIds = array_map(
            function ($item) {
                return $item->entity_id;
            },
            $result
        );

        $this->entityBuffer = array_combine($resultIds, $result);

        return $resultIds;
    }

    /**
     * {@inheritdoc}
     */
    protected function addDependencyData($result)
    {
        parent::addDependencyData($result);

        $customer_group              = $this->dependencies[self::ALIAS_GROUPS][$result->customer_group_id];
        $result->customer_group_code = $customer_group['customer_group_code'];
        $result->customer_group_name = $customer_group['name'];
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
        return 'entity_id';
    }
}
