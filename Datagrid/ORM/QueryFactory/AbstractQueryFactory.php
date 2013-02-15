<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\GridBundle\Datagrid\QueryFactoryInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;

abstract class AbstractQueryFactory implements QueryFactoryInterface
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @return ProxyQueryInterface
     */
    protected function getProxyQuery()
    {
        return new ProxyQuery($this->queryBuilder);
    }

    /**
     * @return ProxyQueryInterface
     */
    public function createQuery()
    {
        return $this->getProxyQuery();
    }
}
