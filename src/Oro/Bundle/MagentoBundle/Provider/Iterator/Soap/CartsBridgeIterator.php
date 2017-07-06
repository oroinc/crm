<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\MagentoBundle\Provider\BatchFilterBag;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class CartsBridgeIterator extends AbstractBridgeIterator
{
    const NOT_LOGGED_IN = 'NOT LOGGED IN';

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
     * @param array $ids
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
            $filters->addStoreFilter($this->getStoresByWebsiteId($this->websiteId));
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
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdFieldName()
    {
        return 'entity_id';
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $this->logger->info(sprintf('Loading Cart by id: %s', $this->key()));

        return $this->current;
    }
}
