<?php

namespace Oro\Bundle\GridBundle\Datagrid;

interface QueryManagerInterface
{
    /**
     * @return ProxyQueryInterface
     */
    public function createQuery();
}
