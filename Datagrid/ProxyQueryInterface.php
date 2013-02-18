<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;

/**
 * Interface used by the Datagrid to build the query
 */
interface ProxyQueryInterface extends BaseProxyQueryInterface
{
    /**
     * @return mixed
     */
    public function getQueryBuilder();
}
