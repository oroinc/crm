<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

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
        parent::addDependencyData($result);

        $customer_group              = $this->dependencies[self::ALIAS_GROUPS][$this->getGroupId($result)];
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

    /**
     * HotFix for BAP-4161, because $result->customer_group_id not present in some cases and we need to set
     * NOT_LOGGED_IN into $customer_group if value does not exists
     *
     * @param $result
     *
     * @return int
     */
    protected function getGroupId($result)
    {
        if (empty($result->customer_group_id)) {
            $groupId = false;

            foreach ($this->dependencies['groups'] as $group) {
                if (self::NOT_LOGGED_IN === $group['customer_group_code']) {
                    $groupId = $group['id'];
                    break;
                }
            }
            unset($group);

            if (false === $groupId) {
                reset($this->dependencies['groups']);
                $currentElement = current($this->dependencies['groups']);

                if (!empty($currentElement)) {
                    $groupId = $currentElement['id'];
                }
            }
        } else {
            $groupId = $result->customer_group_id;
        }

        return $groupId;
    }
}
