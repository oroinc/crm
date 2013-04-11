<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;

use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery as BaseProxyQuery;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

class ProxyQuery extends BaseProxyQuery implements ProxyQueryInterface
{
    /**
     * @var string
     */
    protected $idFieldName;

    /**
     * @var string
     */
    protected $rootAlias;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var array
     */
    protected $sortOrderList = array();

    /**
     * Get query builder
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * Get records total count
     *
     * @return int
     */
    public function getTotalCount()
    {
        $qb = clone $this->getResultIdsQueryBuilder();
        $qb->setFirstResult(null);
        $qb->setMaxResults(null);
        $qb->resetDQLPart('orderBy');

        $ids = $qb->getQuery()->execute();

        return count($ids);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = array(), $hydrationMode = null)
    {
        $query = $this->getResultQueryBuilder()->getQuery();
        return $query->execute($params, $hydrationMode);
    }

    /**
     * Get query builder for result query
     *
     * @return QueryBuilder
     */
    protected function getResultQueryBuilder()
    {
        $qb = clone $this->getQueryBuilder();

        $this->applyWhere($qb);
        $this->applyOrderBy($qb);

        return $qb;
    }

    /**
     * Apply where part on query builder
     *
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    protected function applyWhere(QueryBuilder $queryBuilder)
    {
        $idx = $this->getResultIds();
        if (count($idx) > 0) {
            $queryBuilder->where(sprintf('%s IN (%s)', $this->getIdFieldFQN(), implode(',', $idx)));
            $queryBuilder->resetDQLPart('having');
            $queryBuilder->setParameters(array());
            $queryBuilder->setMaxResults(null);
            $queryBuilder->setFirstResult(null);
        }
    }

    /**
     * Apply order by part
     *
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    protected function applyOrderBy(QueryBuilder $queryBuilder)
    {
        foreach ($this->sortOrderList as $sortOrder) {
            $this->applySortOrder($queryBuilder, $sortOrder);
        }
    }

    /**
     * Apply sorting
     *
     * @param QueryBuilder $queryBuilder
     * @param array $sortOrder
     */
    protected function applySortOrder(QueryBuilder $queryBuilder, array $sortOrder)
    {
        list($sortExpression, $direction, $extraSelect) = $sortOrder;
        $queryBuilder->addOrderBy($sortExpression, $direction);
        if ($extraSelect && !$this->hasSelectItem($queryBuilder, $sortExpression)) {
            $queryBuilder->addSelect($extraSelect);
        }
    }

    /**
     * Checks if select DQL part already has select expression with name
     *
     * @param QueryBuilder $queryBuilder
     * @param string $name
     * @return bool
     */
    protected function hasSelectItem(QueryBuilder $queryBuilder, $name)
    {
        $name = strtolower(trim($name));
        /** @var $select \Doctrine\ORM\Query\Expr\Select */
        foreach ($queryBuilder->getDQLPart('select') as $select) {
            foreach ($select->getParts() as $part) {
                $part = strtolower(trim($part));
                if ($part === $name) {
                    return true;
                } elseif (' as ' . $name === substr($part, -strlen(' as ' . $name))) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Fetches ids of objects that query builder targets
     *
     * @return array
     */
    protected function getResultIds()
    {
        $idx = array();

        $query = $this->getResultIdsQueryBuilder()->getQuery();
        $results = $query->execute(array(), Query::HYDRATE_ARRAY);

        $connection = $this->getQueryBuilder()->getEntityManager()->getConnection();
        foreach ($results as $id) {
            $idx[] = $connection->quote($id[$this->getIdFieldName()]);
        }

        return $idx;
    }

    /**
     * Creates query builder that selects only id's of result objects
     *
     * @return QueryBuilder
     */
    protected function getResultIdsQueryBuilder()
    {
        $qb = clone $this->getQueryBuilder();

        $qb->resetDQLPart('select');
        $qb->addSelect('DISTINCT ' . $this->getIdFieldFQN());
        $this->applyOrderBy($qb);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function addSortOrder(array $parentAssociationMappings, array $fieldMapping, $direction = null)
    {
        $alias = $this->entityJoin($parentAssociationMappings);
        if (!empty($fieldMapping['entityAlias'])) {
            $alias = $fieldMapping['entityAlias'];
        }

        $extraSelect = null;
        if (!empty($fieldMapping['fieldExpression']) && !empty($fieldMapping['fieldName'])) {
            $sortExpression = $fieldMapping['fieldName'];
            $extraSelect = sprintf('%s AS %s', $fieldMapping['fieldExpression'], $fieldMapping['fieldName']);
        } elseif (!empty($fieldMapping['fieldName'])) {
            $sortExpression = $this->getFieldFQN($fieldMapping['fieldName'], $alias);
        } else {
            throw new \LogicException('Cannot add sorting order, unknown field name in $fieldMapping.');
        }

        $this->sortOrderList[] = array($sortExpression, $direction, $extraSelect);
    }

    /**
     * {@inheritdoc}
     */
    public function entityJoin(array $associationMappings)
    {
        $aliases = $this->getQueryBuilder()->getRootAliases();
        $alias = array_shift($aliases);

        $newAlias = 's';

        foreach ($associationMappings as $associationMapping) {
            $newAlias .= '_' . $associationMapping['fieldName'];
            if (!in_array($newAlias, $this->entityJoinAliases)) {
                $this->entityJoinAliases[] = $newAlias;
                $this->getQueryBuilder()
                    ->leftJoin($this->getFieldFQN($associationMapping['fieldName'], $alias), $newAlias);
            }

            $alias = $newAlias;
        }

        return $alias;
    }

    /**
     * Gets the root alias of the query
     *
     * @return string
     */
    protected function getRootAlias()
    {
        if (!$this->rootAlias) {
            $this->rootAlias = current($this->getQueryBuilder()->getRootAliases());
        }
        return $this->rootAlias;
    }

    /**
     * Retrieve the column id of the targeted class
     *
     * @return string
     */
    protected function getIdFieldName()
    {
        if (!$this->idFieldName) {
            /** @var $from \Doctrine\ORM\Query\Expr\From */
            $from  = current($this->getQueryBuilder()->getDQLPart('from'));
            $class = $from->getFrom();

            $idNames = $this->getQueryBuilder()
                ->getEntityManager()
                ->getMetadataFactory()
                ->getMetadataFor($class)
                ->getIdentifierFieldNames();

            $this->idFieldName = current($idNames);
        }

        return $this->idFieldName;
    }

    /**
     * Get id field fully qualified name
     *
     * @return string
     */
    protected function getIdFieldFQN()
    {
        return $this->getFieldFQN($this->getIdFieldName());
    }

    /**
     * Get fields fully qualified name
     *
     * @param string $fieldName
     * @param string|null $parentAlias
     * @return string
     */
    protected function getFieldFQN($fieldName, $parentAlias = null)
    {
        if (strpos($fieldName, '.') === false) { // add the current alias
            $fieldName = ($parentAlias ? : $this->getRootAlias()) . '.' . $fieldName;
        }
        return $fieldName;
    }
}
