<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Dependency\CartDependencyManager;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class CartsBridgeIterator extends AbstractBridgeIterator
{
    const NOT_LOGGED_IN = 'NOT LOGGED IN';

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

        $result = $this->transport->call(SoapTransport::ACTION_ORO_CART_LIST, $filters);
        $result = $this->processCollectionResponse($result);

        $that      = $this;
        $resultIds = array_map(
            function (&$item) use ($that) {
                $item->items = $that->processCollectionResponse($item->items);

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
        CartDependencyManager::addDependencyData($result, $this->transport);
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdFieldName()
    {
        return 'entity_id';
    }
}
