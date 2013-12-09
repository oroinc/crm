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
}
