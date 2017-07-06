<?php

namespace Oro\Bundle\MagentoBundle\Provider\Connector;

use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;

/**
 * Interface MagentoConnectorInterface
 *
 * @package Oro\Bundle\MagentoBundle\Provider
 * This interface should be implemented by magento related connectors
 * Contains just general constants
 */
interface MagentoConnectorInterface extends ConnectorInterface
{
    const STORE_TYPE   = 'Oro\\Bundle\\MagentoBundle\\Entity\\Store';
    const WEBSITE_TYPE = 'Oro\\Bundle\\MagentoBundle\\Entity\\Website';

    const ORDER_TYPE                    = 'Oro\\Bundle\\MagentoBundle\\Entity\\Order';
    const CREDIT_MEMO_TYPE              = 'Oro\\Bundle\\MagentoBundle\\Entity\\CreditMemo';
    const ORDER_ADDRESS_TYPE            = 'Oro\\Bundle\\MagentoBundle\\Entity\\OrderAddress';
    const ORDER_ADDRESS_COLLECTION_TYPE = 'ArrayCollection<Oro\\Bundle\\MagentoBundle\\Entity\\OrderAddress>';
    const ORDER_ITEM_TYPE               = 'Oro\\Bundle\\MagentoBundle\\Entity\\OrderItem';
    const ORDER_ITEM_COLLECTION_TYPE    = 'ArrayCollection<Oro\\Bundle\\MagentoBundle\\Entity\\OrderItem>';

    const REGION_TYPE = 'Oro\\Bundle\\MagentoBundle\\Entity\\Region';

    const CUSTOMER_TYPE           = 'Oro\\Bundle\\MagentoBundle\\Entity\\Customer';
    const CUSTOMER_GROUPS_TYPE    = 'Oro\\Bundle\\MagentoBundle\\Entity\\CustomerGroup';
    const CONTACT_ADDRESSES_TYPE  = 'ArrayCollection<Oro\\Bundle\\ContactBundle\\Entity\\ContactAddress>';
    const CUSTOMER_ADDRESS_TYPE   = 'Oro\\Bundle\\MagentoBundle\\Entity\\Address';
    const CUSTOMER_ADDRESSES_TYPE = 'ArrayCollection<Oro\\Bundle\\MagentoBundle\\Entity\\Address>';

    const CART_TYPE         = 'Oro\\Bundle\\MagentoBundle\\Entity\\Cart';
    const CART_ITEM_TYPE    = 'Oro\\Bundle\\MagentoBundle\\Entity\\CartItem';
    const CART_STATUS_TYPE  = 'Oro\\Bundle\\MagentoBundle\\Entity\\CartStatus';
    const CART_ITEMS_TYPE   = 'ArrayCollection<Oro\\Bundle\\MagentoBundle\\Entity\\CartItem>';
    const CART_ADDRESS_TYPE = 'Oro\\Bundle\\MagentoBundle\\Entity\\CartAddress';
}
