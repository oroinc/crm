<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

class CustomerConnector extends AbstractApiBasedConnector
{
    const ENTITY_NAME         = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Customer';
    const JOB_VALIDATE_IMPORT = 'mage_customer_import_validation';
    const JOB_IMPORT          = 'mage_customer_import';
    const CONNECTOR_LABEL     = 'orocrm.magento.connector.customer.label';

    /**
     * {@inheritdoc}
     */
    protected function loadDependencies()
    {
        foreach ([self::ALIAS_GROUPS, self::ALIAS_STORES, self::ALIAS_WEBSITES] as $item) {
            switch ($item) {
                case self::ALIAS_GROUPS:
                    $this->dependencies[self::ALIAS_GROUPS] = $this->getCustomerGroups();
                    break;
                case self::ALIAS_STORES:
                    $this->dependencies[self::ALIAS_STORES] = $this->storeConnector->getStores();
                    break;
                case self::ALIAS_WEBSITES:
                    $this->dependencies[self::ALIAS_WEBSITES] = $this->storeConnector->getWebsites(
                        $this->dependencies[self::ALIAS_STORES]
                    );
                    break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getList($filters = [], $limit = null, $idsOnly = false)
    {
        $result = $this->call(MagentoConnectorInterface::ACTION_CUSTOMER_LIST, $filters);

        if ($idsOnly) {
            $result = array_map(
                function ($item) {
                    return is_object($item) ? $item->customer_id : $item['customer_id'];
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
    public function getData($id, $dependenciesInclude = false, $onlyAttributes = null)
    {
        $result = $this->call(MagentoConnectorInterface::ACTION_CUSTOMER_INFO, [$id, $onlyAttributes]);

        if ($dependenciesInclude) {
            $result->addresses = $this->getCustomerAddressData($id);
            foreach ($result->addresses as $key => $val) {
                $result->addresses[$key] = (array)$val;
            }
        }

        $result->group               = $this->dependencies[self::ALIAS_GROUPS][$result->group_id];
        $result->group['originId']   = $result->group['customer_group_id'];
        $result->store               = $this->dependencies[self::ALIAS_STORES][$result->store_id];
        $result->store['originId']   = $result->store_id;
        $result->website             = $this->dependencies[self::ALIAS_WEBSITES][$result->website_id];
        $result->website['originId'] = $result->website['id'];

        return (array)$result;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerAddressData($customerId)
    {
        return $this->call(MagentoConnectorInterface::ACTION_ADDRESS_LIST, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroups($groupId = null)
    {
        if (!empty($this->dependencies[self::ALIAS_GROUPS])) {
            $groups = $this->dependencies[self::ALIAS_GROUPS];
        } else {
            $result = $this->call(MagentoConnectorInterface::ACTION_GROUP_LIST);

            $groups = [];
            foreach ($result as $item) {
                $item->id                         = $item->customer_group_id;
                $item->name                       = $item->customer_group_code;
                $groups[$item->customer_group_id] = (array)$item;
            }
        }

        if (!is_null($groupId) && isset($groups[$groupId])) {
            $result = [$groupId => $groups[$groupId]];
        } else {
            $result = $groups;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return 'customer';
    }
}
