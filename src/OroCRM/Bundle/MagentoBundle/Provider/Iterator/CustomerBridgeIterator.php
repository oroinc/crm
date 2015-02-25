<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Dependency\CustomerDependencyManager;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class CustomerBridgeIterator extends AbstractBridgeIterator
{
    /**
     * {@inheritdoc}
     */
    protected function applyFilter()
    {
        if ($this->websiteId !== StoresSoapIterator::ALL_WEBSITES) {
            $this->filter->addWebsiteFilter([$this->websiteId]);
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

        $result = $this->transport->call(SoapTransport::ACTION_ORO_CUSTOMER_LIST, $filters);
        $result = $this->processCollectionResponse($result);

        $that               = $this;
        $resultIds          = array_map(
            function (&$item) use ($that) {
                $item->addresses = $that->processCollectionResponse($item->addresses);

                return $item->customer_id;
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
        CustomerDependencyManager::addDependencyData($result, $this->transport->getDependencies());
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdFieldName()
    {
        return 'customer_id';
    }
}
