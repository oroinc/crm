<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class OrderSoapIterator extends AbstractPageableSoapIterator
{
    /**
     * {@inheritdoc}
     */
    public function getEntityIds()
    {
        $filters = $this->getBatchFilter($this->lastSyncDate, [], $this->getStoresByWebsiteId($this->websiteId));

        $result = $this->transport->call(SoapTransport::ACTION_ORDER_LIST, [$filters]);
        $result = is_array($result) ? $result : [];

        $idFieldName = $this->getIdFieldName();
        $result      = array_map(
            function ($item) use ($idFieldName) {
                $inc = is_object($item) ? $item->increment_id : $item['increment_id'];
                $id  = is_object($item) ? $item->order_id : $item['order_id'];

                return (object)['increment_id' => $inc, $idFieldName => $id];
            },
            $result
        );

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntity($id)
    {
        $result = $this->transport->call(SoapTransport::ACTION_ORDER_INFO, [$id->increment_id]);

        // fill related entities data, needed to create full representation of magento store state in this time
        // flat array structure will be converted by data converter
        $store                      = $this->dependencies[self::ALIAS_STORES][$result->store_id];
        $website                    = $this->dependencies[self::ALIAS_WEBSITES][$store['website_id']];
        $result->store_code         = $store['code'];
        $result->store_storename    = $result->store_name;
        $result->store_website_id   = $website['id'];
        $result->store_website_code = $website['code'];
        $result->store_website_name = $website['name'];

        $result->payment_method = isset($result->payment, $result->payment->method) ? $result->payment->method : null;

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
