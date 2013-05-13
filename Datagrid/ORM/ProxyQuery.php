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
        $this->applyOrderByParameters($qb);

        return $qb;
    }

    /**
     * Apply where part on query builder
     *
     * @param QueryBuilder $qb
     * @return QueryBuilder
     */
    protected function applyWhere(QueryBuilder $qb)
    {
        $idx = $this->getResultIds();
        if (count($idx) > 0) {
            $qb->where(sprintf('%s IN (%s)', $this->getIdFieldFQN(), implode(',', $idx)));
            $qb->resetDQLPart('having');
            $qb->setMaxResults(null);
            $qb->setFirstResult(null);
            // Since DQL has been changed, some parameters potentially are not used anymore.
            $this->fixUnusedParameters($qb);
        }
    }

    /**
     * Removes unused parameters from query builder
     *
     * @param QueryBuilder $qb
     */
    protected function fixUnusedParameters(QueryBuilder $qb)
    {
        $dql = $qb->getDQL();
        $usedParameters = array();
        /** @var $parameter \Doctrine\ORM\Query\Parameter */
        foreach ($qb->getParameters() as $parameter) {
            if ($this->dqlContainsParameter($dql, $parameter->getName())) {
                $usedParameters[$parameter->getName()] = $parameter->getValue();
            }
        }
        $qb->setParameters($usedParameters);
    }

    /**
     * Returns TRUE if $dql contains usage of parameter with $parameterName
     *
     * @param string $dql
     * @param string $parameterName
     * @return bool
     */
    protected function dqlContainsParameter($dql, $parameterName)
    {
        if (is_numeric($parameterName)) {
            $pattern = sprintf('/\?%s[^\w]/', preg_quote($parameterName));
        } else {
            $pattern = sprintf('/\:%s[^\w]/', preg_quote($parameterName));
        }
        return (bool)preg_match($pattern, $dql . ' ');
    }

    /**
     * Apply order by part
     *
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    protected function applyOrderByParameters(QueryBuilder $queryBuilder)
    {
        foreach ($this->sortOrderList as $sortOrder) {
            $this->applySortOrderParameters($queryBuilder, $sortOrder);
        }
    }

    /**
     * Apply sorting
     *
     * @param QueryBuilder $queryBuilder
     * @param array $sortOrder
     */
    protected function applySortOrderParameters(QueryBuilder $queryBuilder, array $sortOrder)
    {
        list($sortExpression, $extraSelect) = $sortOrder;
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

        // Apply orderBy before change select, because it can contain some expressions from select as aliases
        $this->applyOrderByParameters($qb);

        $selectExpressions = array('DISTINCT ' . $this->getIdFieldFQN());
        // We must leave expressions used in having
        $selectExpressions = array_merge($selectExpressions, $this->getSelectExpressionsUsedByAliases($qb));
        $qb->select($selectExpressions);

        // Since DQL has been changed, some parameters potentially are not used anymore.
        $this->fixUnusedParameters($qb);

        return $qb;
    }

    /**
     * Returns select expressions used in DQL as aliases
     *
     * Example of usage:
     *
     * Assume $qb holds DQL as below:
     * SELECT u, CASE WHEN :group MEMBER OF u.groups THEN 1 ELSE 0 END AS hasGroup FROM User HAVING hasGroup = 1
     *
     * Then method will return:
     * array("CASE WHEN :group MEMBER OF u.groups THEN 1 ELSE 0 END AS hasGroup")
     *
     * @param QueryBuilder $qb
     * @return array
     */
    protected function getSelectExpressionsUsedByAliases(QueryBuilder $qb)
    {
        $result = array();
        $qb = clone $qb;
        $selectExpressions = $this->getSelectExpressions($qb);
        $dqlWithoutSelect = $qb->resetDQLPart('select')->getDQL();
        foreach ($selectExpressions as $expression) {
            if (preg_match('/^.* AS ([\w]+)$/i', $expression, $matches)) {
                $alias = $matches[1];
                if (preg_match(sprintf('/[^\w]%s[^\w]/', preg_quote($alias)), $dqlWithoutSelect . ' ')) {
                    $result[] = $expression;
                }
            }
        }
        return $result;
    }

    /**
     * Get list of select DQL part expressions strings of QueryBuilder.
     *
     * Example of usage:
     *
     * Assume $qb holds DQL as below:
     * SELECT u.name, u.email FROM User
     *
     * Then method will return:
     * array("u.name", "u.email")
     *
     * @param QueryBuilder $qb
     * @return array
     */
    protected function getSelectExpressions(QueryBuilder $qb)
    {
        $result = array();
        /** @var $selectPart \Doctrine\ORM\Query\Expr\Select */
        foreach ($qb->getDQLPart('select') as $selectPart) {
            foreach ($selectPart->getParts() as $part) {
                if (is_string($part)) {
                    $result = array_merge($result, array_map('trim', explode(',', $part)));
                } else {
                    $result[] = $part;
                }
            }
        }
        return $result;
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

        $this->getQueryBuilder()->addOrderBy($sortExpression, $direction);
        $this->sortOrderList[] = array($sortExpression, $extraSelect);
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
