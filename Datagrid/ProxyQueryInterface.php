<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;

/**
 * Interface used by the Datagrid to build the query
 */
interface ProxyQueryInterface extends BaseProxyQueryInterface
{
    /**
     * Adds sorting order
     *
     * @param array $parentAssociationMappings
     * @param array $fieldMapping
     * @param string $direction
     */
    public function addSortOrder(array $parentAssociationMappings, array $fieldMapping, $direction = null);

    /**
     * Get records total count
     *
     * @return array
     */
    public function getTotalCount();
}
