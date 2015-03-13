<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use Oro\Bundle\IntegrationBundle\Utils\ConverterUtils;

use OroCRM\Bundle\MagentoBundle\Provider\BatchFilterBag;
use OroCRM\Bundle\MagentoBundle\Provider\Dependency\CustomerDependencyManager;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class CustomerSoapIterator extends AbstractPageableSoapIterator
{
    /**
     * {@inheritdoc}
     */
    public function getEntityIds()
    {
        $filters = $this->getBatchFilter($this->lastSyncDate, [$this->websiteId]);

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

        $this->loadByFilters($filters->getAppliedFilters());
    }

    /**
     * @param array $filters
     */
    protected function loadByFilters(array $filters)
    {
        $result = $this->transport->call(SoapTransport::ACTION_CUSTOMER_LIST, $filters);
        $result = $this->processCollectionResponse($result);

        $ids = array_map(
            function ($item) {
                return is_object($item) ? $item->customer_id : $item['customer_id'];
            },
            $result
        );

        $this->entityBuffer = array_combine($ids, $result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntity($id)
    {
        if (!array_key_exists($id, $this->entityBuffer)) {
            $this->logger->warning(sprintf('Entity with id "%s" was not found', $id));

            return false;
        }

        $result = $this->entityBuffer[$id];
        $this->addDependencyData($result);

        return ConverterUtils::objectToArray($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function addDependencyData($result)
    {
        CustomerDependencyManager::addDependencyData($result, $this->transport);
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
