<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

interface CustomerConnectorInterface extends MagentoConnectorInterface
{
    /**
     * Get customer list
     *
     * @param array $filters
     * @param int   $limit
     * @param bool  $idsOnly
     *
     * @return array
     */
    public function getList($filters = [], $limit = null, $idsOnly = true);

    /**
     * @param int   $id
     * @param bool  $isAddressesIncluded
     * @param array|null $onlyAttributes array of needed attributes or null to get all list
     *
     * @return mixed
     */
    public function getData($id, $isAddressesIncluded = false, $onlyAttributes = null);

    /**
     * @param $customerId
     *
     * @return mixed
     */
    public function getCustomerAddressData($customerId);

    /**
     * Return customer groups assoc array
     * with magento group ids as keys and codes as values
     *
     * @param null $groupId if specified, only data for this group will be returned
     *
     * @return mixed
     */
    public function getCustomerGroups($groupId = null);
}
