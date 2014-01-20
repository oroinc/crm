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
    const STORE_TYPE                    = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Store';
    const WEBSITE_TYPE                  = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Website';

    const ORDER_TYPE                    = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Order';
    const ORDER_ADDRESS_COLLECTION_TYPE = 'ArrayCollection<OroCRM\Bundle\MagentoBundle\Entity\OrderAddress>';
    const ORDER_ITEM_TYPE               = 'OroCRM\Bundle\MagentoBundle\Entity\OrderItem';
    const ORDER_ITEM_COLLECTION_TYPE    = 'ArrayCollection<OroCRM\Bundle\MagentoBundle\Entity\OrderItem>';
}
