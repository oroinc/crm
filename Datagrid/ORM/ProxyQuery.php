<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
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

        // todo : check how doctrine behave, potential SQL injection here ...
        if ($this->getSortBy()) {
            $sortBy = $this->getSortBy();
            if (strpos($sortBy, '.') === false) { // add the current alias
                $sortBy = $queryBuilder->getRootAlias() . '.' . $sortBy;
            }
            $queryBuilder->addOrderBy($sortBy, $this->getSortOrder());
        }

        return $this->getFixedQueryBuilder($queryBuilder)->getQuery()->execute($params, $hydrationMode);
    }

    /**
     * This method alters the query to return a clean set of object with a working
     * set of Object
     *
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getFixedQueryBuilder(QueryBuilder $queryBuilder)
    {
        $queryBuilderId = clone $queryBuilder;

        // step 1 : retrieve the targeted class
        $from  = $queryBuilderId->getDQLPart('from');
        $class = $from[0]->getFrom();

        // step 2 : retrieve the column id
        $idName = current(
            $queryBuilderId->getEntityManager()->getMetadataFactory()->getMetadataFor($class)->getIdentifierFieldNames()
        );

        // step 3 : retrieve the different subjects id
        $select = sprintf('%s.%s', $queryBuilderId->getRootAlias(), $idName);
        $queryBuilderId->resetDQLPart('select');
        $queryBuilderId->resetDQLPart('orderBy');
        $queryBuilderId->add('select', 'DISTINCT ' . $select);

        //for SELECT DISTINCT, ORDER BY expressions must appear in select list
        /* Consider
            SELECT DISTINCT x FROM tab ORDER BY y;
        For any particular x-value in the table there might be many different y
        values.  Which one will you use to sort that x-value in the output?
        */
        // todo : check how doctrine behave, potential SQL injection here ...
        if ($this->getSortBy()) {
            $sortBy = $this->getSortBy();
            if (strpos($sortBy, '.') === false) { // add the current alias
                $sortBy = $queryBuilderId->getRootAlias() . '.' . $sortBy;
            }
            $sortBy .= ' AS __order_by';
            $queryBuilderId->addSelect($sortBy);
        }

        $results    = $queryBuilderId->getQuery()->execute(array(), Query::HYDRATE_ARRAY);
        $idx        = array();
        $connection = $queryBuilder->getEntityManager()->getConnection();
        foreach ($results as $id) {
            $idx[] = $connection->quote($id[$idName]);
        }

        // step 4 : alter the query to match the targeted ids
        if (count($idx) > 0) {
            $queryBuilder->andWhere(sprintf('%s IN (%s)', $select, implode(',', $idx)));
            $queryBuilder->setMaxResults(null);
            $queryBuilder->setFirstResult(null);
        }

        return $queryBuilder;
    }
}
