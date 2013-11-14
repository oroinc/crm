<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

interface CustomerConnectorInterface
{
    const ACTION_CUSTOMER_LIST = 'customerCustomerList';
    const ACTION_CUSTOMER_INFO = 'customerCustomerInfo';
    const ACTION_ADDRESS_LIST  = 'customerAddressList';
    const ACTION_GROUP_LIST    = 'customerGroupList';
    const ACTION_STORE_LIST    = 'storeList';

    /**
     * Get customer list
     *
     * @param array $filters
     * @return array
     */
    public function getCustomersList($filters = []);

    /**
     * @param $id
     * @param bool $isAddressesIncluded
     * @param bool $isGroupsIncluded
     * @param array $onlyAttributes
     * @return mixed
     */
    public function getCustomerData($id, $isAddressesIncluded = false, $isGroupsIncluded = false, $onlyAttributes = []);

    /**
     * @param $customerId
     * @return mixed
     */
    public function getCustomerAddressData($customerId);

    /**
     * Return customer groups assoc array
     * with magento group ids as keys and codes as values
     *
     * @param null $groupId if specified, only data for this group will be returned
     * @return mixed
     */
    public function getCustomerGroups($groupId = null);

    public function saveCustomerData();

    public function saveCustomerAddress();
}
