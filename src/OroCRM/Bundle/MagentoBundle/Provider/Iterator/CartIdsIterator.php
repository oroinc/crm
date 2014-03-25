<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

class CartIdsIterator extends AbstractBridgeIterator
{
    /**
     * {@inheritdoc}
     */
    protected function applyFilter()
    {
        $this->filter->addStoreFilter($this->getStoresByWebsiteId($this->websiteId));
        // skip empty carts
        $this->filter->addComplexFilter(
            'grand_total',
            [
                'key'   => 'grand_total',
                'value' => [
                    'key'   => 'gt',
                    'value' => 0,
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDependencies()
    {
        return [
            self::ALIAS_STORES   => iterator_to_array($this->transport->getStores())
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
