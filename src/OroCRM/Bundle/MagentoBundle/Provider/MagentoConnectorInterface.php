<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

/**
 * Interface MagentoConnectorInterface
 *
 * @package OroCRM\Bundle\MagentoBundle\Provider
 * This interface should be implemented by magento related connectors
 * Contains just general constants
 */
interface MagentoConnectorInterface
{
    const ALIAS_GROUPS   = 'groups';
    const ALIAS_STORES   = 'stores';
    const ALIAS_WEBSITES = 'websites';
    const ALIAS_REGIONS  = 'regions';

    const ACTION_CUSTOMER_LIST = 'customerCustomerList';
    const ACTION_CUSTOMER_INFO = 'customerCustomerInfo';
    const ACTION_ADDRESS_LIST  = 'customerAddressList';
    const ACTION_GROUP_LIST    = 'customerGroupList';
    const ACTION_STORE_LIST    = 'storeList';
    const ACTION_ORDER_LIST    = 'salesOrderList';
}
