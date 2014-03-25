<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;

/**
 * Interface MagentoConnectorInterface
 *
 * @package OroCRM\Bundle\MagentoBundle\Provider
 * This interface should be implemented by magento related connectors
 * Contains just general constants
 */
interface MagentoConnectorInterface extends ConnectorInterface
{
    const STORE_TYPE   = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Store';
    const WEBSITE_TYPE = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Website';

    const ORDER_TYPE                    = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Order';
    const ORDER_ADDRESS_TYPE            = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\OrderAddress';
    const ORDER_ADDRESS_COLLECTION_TYPE = 'ArrayCollection<OroCRM\\Bundle\\MagentoBundle\\Entity\\OrderAddress>';
    const ORDER_ITEM_TYPE               = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\OrderItem';
    const ORDER_ITEM_COLLECTION_TYPE    = 'ArrayCollection<OroCRM\\Bundle\\MagentoBundle\\Entity\\OrderItem>';

    const REGION_TYPE = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Region';

    const CUSTOMER_TYPE           = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Customer';
    const CUSTOMER_GROUPS_TYPE    = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\CustomerGroup';
    const CONTACT_ADDRESSES_TYPE  = 'ArrayCollection<OroCRM\\Bundle\\ContactBundle\\Entity\\ContactAddress>';
    const CUSTOMER_ADDRESS_TYPE   = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Address';
    const CUSTOMER_ADDRESSES_TYPE = 'ArrayCollection<OroCRM\\Bundle\\MagentoBundle\\Entity\\Address>';

    const CART_TYPE         = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Cart';
    const CART_ITEM_TYPE    = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\CartItem';
    const CART_STATUS_TYPE  = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\CartStatus';
    const CART_ITEMS_TYPE   = 'ArrayCollection<OroCRM\\Bundle\\MagentoBundle\\Entity\\CartItem>';
    const CART_ADDRESS_TYPE = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\CartAddress';
}
