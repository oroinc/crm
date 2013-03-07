<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM;

use Doctrine\ORM\QueryBuilder;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery as BaseProxyQuery;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

class ProxyQuery extends BaseProxyQuery implements ProxyQueryInterface
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * {@inheritdoc}
     */
    public function entityJoin(array $associationMappings)
    {
        $aliases = $this->queryBuilder->getRootAliases();
        $alias = array_shift($aliases);

        $newAlias = 's';

        foreach ($associationMappings as $associationMapping) {
            $newAlias .= '_' . $associationMapping['fieldName'];
            if (!in_array($newAlias, $this->entityJoinAliases)) {
                $this->entityJoinAliases[] = $newAlias;
                $this->queryBuilder->leftJoin(sprintf('%s.%s', $alias, $associationMapping['fieldName']), $newAlias);
            }

            $alias = $newAlias;
        }

        return $alias;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = array(), $hydrationMode = null)
    {
        // always clone the original queryBuilder
        $queryBuilder = clone $this->queryBuilder;
        $queryBuilder->setMaxResults(null);
        $queryBuilder->setFirstResult(null);

        // there is no need to perform additional filtering here
        return $queryBuilder->getQuery()->execute($params, $hydrationMode);
    }
}
