<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class OrderSoapIterator extends AbstractPageableSoapIterator
{
    /**
     * {@inheritdoc}
     */
    protected function getBatchFilter($websiteId, \DateTime $endDate, $format = 'Y-m-d H:i:s')
    {
        return parent::getBatchFilter(
            [
                'field' => 'store_id',
                'value' => $this->getStoresByWebsiteId($websiteId)
            ],
            $endDate,
            $format
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getList($filters = [], $limit = null, $idsOnly = true)
    {
        $result = $this->transport->call(SoapTransport::ACTION_ORDER_LIST, $filters);

        if ($idsOnly) {
            $result = array_map(
                function ($item) {
                    $inc = is_object($item) ? $item->increment_id : $item['increment_id'];
                    $id  = is_object($item) ? $item->order_id : $item['order_id'];

                    return (object)['increment_id' => $inc, 'entity_id' => $id];
                },
                $result
            );
        }

        if ((int)$limit > 0) {
            $result = array_slice($result, 0, $limit);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getData($id, $dependenciesInclude = false, $onlyAttributes = null)
    {
        $result = $this->transport->call(SoapTransport::ACTION_ORDER_INFO, [$id->increment_id, $onlyAttributes]);

        // fill related entities data, needed to create full representation of magento store state in this time
        // flat array structure will be converted by data converter
        $store                      = $this->getStoreDataById($result->store_id);
        $result->store_code         = $store['code'];
        $result->store_storename    = $result->store_name;
        $result->store_website_id   = $store['website']['id'];
        $result->store_website_code = $store['website']['code'];
        $result->store_website_name = $store['website']['name'];

        $result->payment_method = isset($result->payment, $result->payment->method) ? $result->payment->method : null;

        $result = ConverterUtils::objectToArray($result);

        return (array)$result;
    }


    /**
     * @param int $id
     *
     * @return array
     */
    protected function getStoreDataById($id)
    {
        $store            = $this->dependencies[self::ALIAS_STORES][$id];
        $store['website'] = $this->dependencies[self::ALIAS_WEBSITES][$store['website_id']];

        return $store;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadDependencies()
    {
        foreach ([self::ALIAS_STORES, self::ALIAS_WEBSITES] as $item) {
            switch ($item) {
                case self::ALIAS_STORES:
                    $this->dependencies[self::ALIAS_STORES] = iterator_to_array($this->transport->getStores());
                    break;
                case self::ALIAS_WEBSITES:
                    $this->dependencies[self::ALIAS_WEBSITES] = iterator_to_array($this->transport->getWebsites());
                    break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdFieldName()
    {
        return 'order_id';
    }
}
