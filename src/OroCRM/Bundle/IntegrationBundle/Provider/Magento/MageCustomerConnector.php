<?php

namespace OroCRM\Bundle\IntegrationBundle\Provider\Magento;

use OroCRM\Bundle\IntegrationBundle\Provider\AbstractConnector;

class MageCustomerConnector extends AbstractConnector
{
    /**
     * Get customer list
     *
     * @param array $filters
     * @return array
     */
    public function getCustomersList($filters = [])
    {
        return $this->call('customerCustomerList', $filters);
    }

    /**
     * @param $id
     * @param bool $isAddressesIncluded
     * @param bool $isGroupsIncluded
     * @param array $onlyAttributes
     * @return mixed
     */
    public function getCustomerData($id, $isAddressesIncluded = false, $isGroupsIncluded = false, $onlyAttributes = [])
    {
        $result = $this->call('customerCustomerInfo', [$id, $onlyAttributes]);

        if ($isAddressesIncluded) {
            $result->addresses = $this->getCustomerAddressData($id);
        }

        if ($isGroupsIncluded) {
            $result->groups = $this->getCustomerGroups($result->group_id);
            $result->group_name = $result->groups[$result->group_id];
        }

        return $result;
    }

    /**
     * @param $customerId
     * @return mixed
     */
    public function getCustomerAddressData($customerId)
    {
        return $this->call('customerAddressList', $customerId);
    }

    /**
     * Return customer groups assoc array
     * with magento group ids as keys and codes as values
     *
     * @param null $groupId if specified, only data for this group will be returned
     * @return mixed
     */
    public function getCustomerGroups($groupId = null)
    {
        $result = $this->call('customerGroupList');

        $groups = [];
        foreach ($result as $item) {
            $groups[$item->customer_group_id] = $item->customer_group_code;
        }

        if (!is_null($groupId) && isset($groups[$groupId])) {
            $result = [$groupId => $groups[$groupId]];
        } else {
            $result = $groups;
        }

        return  $result;
    }

    public function saveCustomerData()
    {
        // TODO: implement create/update customer data
        return [];
    }

    public function saveCustomerAddress()
    {
        // TODO: implement create/update customer address
    }
}
