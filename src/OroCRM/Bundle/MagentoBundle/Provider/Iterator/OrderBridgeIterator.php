<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;

class OrderBridgeIterator extends AbstractBridgeIterator
{
    /** @var \stdClass[] Entities buffer got from pagable remote */
    protected $entityBuffer = null;

    /**
     * {@inheritdoc}
     */
    protected function getEntityIds()
    {
        $this->filter
            ->addStoreFilter($this->getStoresByWebsiteId($this->websiteId))
            ->addDateFilter($this->mode == self::IMPORT_MODE_INITIAL, $this->lastSyncDate);

        $result = $this->call(
            SoapTransport::ACTION_ORO_ORDER_LIST,
            [$this->filter->getAppliedFilters()],
            ['page' => $this->getCurrentPage(), 'pageSize' => self::DEFAULT_PAGE_SIZE]
        );

        $buffer = [];
        $result = array_map(
            function ($item) use ($buffer) {
                $buffer[$this->order_id] = $item;

                return (object)['increment_id' => $item->increment_id, 'entity_id' => $item->order_id];
            },
            $result
        );
        $this->entityBuffer = $buffer;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntity($id)
    {
        $result = $this->entityBuffer[$id];

        $this->addDependencyData($result);

        return ConverterUtils::objectToArray($result);
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
