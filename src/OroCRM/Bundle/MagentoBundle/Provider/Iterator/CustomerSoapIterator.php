<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class CustomerSoapIterator extends AbstractPageableSoapIterator
{
    /**
     * {@inheritdoc}
     */
    public function getEntityIds()
    {
        $filters = $this->getBatchFilter($this->lastSyncDate, [$this->websiteId]);

        $result = $this->transport->call(SoapTransport::ACTION_CUSTOMER_LIST, $filters);
        $result = $this->processCollectionResponse($result);

        $ids = array_map(
            function ($item) {
                return is_object($item) ? $item->customer_id : $item['customer_id'];
            },
            $result
        );

        $this->entityBuffer = array_combine($ids, $result);

        return $ids;
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
    protected function addDependencyData($result)
    {
        /**
         * @TODO move to converter
         */
        // fill related entities data, needed to create full representation of magento store state in this time
        // flat array structure will be converted by data converter
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
            self::ALIAS_GROUPS   => iterator_to_array($this->transport->getCustomerGroups())
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdFieldName()
    {
        return 'customer_id';
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerAddressData($customerId)
    {
        $result = $this->transport->call(SoapTransport::ACTION_ADDRESS_LIST, ['customerId' => $customerId]);
        $result = $this->processCollectionResponse($result);

        return $result;
    }
}
