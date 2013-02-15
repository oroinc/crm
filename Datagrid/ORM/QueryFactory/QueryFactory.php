<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory;

use Doctrine\ORM\QueryBuilder;

class QueryFactory extends AbstractQueryFactory
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }
}
