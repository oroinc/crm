<?php

namespace Oro\Bundle\SearchBundle\Engine\Orm;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\SearchBundle\Query\Query;

abstract class BaseDriver extends FunctionNode
{
    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManager         $em
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function initRepo(EntityManager $em, ClassMetadata $class)
    {
        $this->entityName = $class->name;
        $this->em = $em;
    }

    /**
     * Create a new QueryBuilder instance that is prepopulated for this entity name
     *
     * @param string $alias
     *
     * @return QueryBuilder $qb
     */
    public function createQueryBuilder($alias)
    {
        return $this->em->createQueryBuilder()
            ->select($alias)
            ->from($this->entityName, $alias);
    }

    /**
     * Search query by Query builder object
     *
     * @param \Oro\Bundle\SearchBundle\Query\Query $query
     *
     * @return array
     */
    public function search(Query $query)
    {
        $qb = $this->getRequestQB($query);
        $qb->distinct(true);

        // set max results count
        if ($query->getMaxResults() > 0) {
            $qb->setMaxResults($query->getMaxResults());
        }

        // set first result offset
        if ($query->getFirstResult() > 0) {
            $qb->setFirstResult($query->getFirstResult());
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Get count of records without limit parameters in query
     *
     * @param \Oro\Bundle\SearchBundle\Query\Query $query
     *
     * @return integer
     */
    public function getRecordsCount(Query $query)
    {
        $qb = $this->getRequestQB($query);
        $qb->select($qb->expr()->countDistinct('search.id'));

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Add text search to qb
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param integer                    $index
     * @param array                      $searchCondition
     */
    protected function addTextField(QueryBuilder $qb, $index, $searchCondition)
    {
        $joinAlias = 'textFields' . $index;
        $qb->join('search.textFields', $joinAlias);
        $useFieldName = $searchCondition['fieldName'] == '*' ? false : true;
        if ($searchCondition['type'] == 'and') {
            $qb->andWhere($this->createStringQuery($joinAlias, $index, $useFieldName));
            $this->setFieldValueStringParameter($qb, $index, $searchCondition['fieldValue']);
        } else {
            $qb->orWhere($this->createStringQuery($joinAlias, $index, $useFieldName));
            $qb->setParameter('value' . $index, $searchCondition['fieldValue']);
        }
        if ($useFieldName) {
            $qb->setParameter('field' . $index, $searchCondition['fieldName']);
        }
    }

    /**
     * Create search string for string parameters
     *
     * @param string  $joinAlias
     * @param integer $index
     * @param bool    $useFieldName
     *
     * @return string
     */
    protected function createStringQuery($joinAlias, $index, $useFieldName = true)
    {
        $stringQuery = '';
        if ($useFieldName) {
            $stringQuery = $joinAlias . '.field = :field' . $index . ' AND ';
        }

        return $stringQuery . $joinAlias . '.value LIKE :value' . $index;
    }

    /**
     * Set string parameter for qb
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param integer                    $index
     * @param string                     $fieldValue
     */
    protected function setFieldValueStringParameter(QueryBuilder $qb, $index, $fieldValue)
    {
        $qb->setParameter('value' . $index, '%' . str_replace(' ', '%', $fieldValue) . '%');
    }

    /**
     * Add non string search to qb
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param integer                    $index
     * @param array                      $searchCondition
     */
    protected function addNonTextField(QueryBuilder $qb, $index, $searchCondition)
    {
        $joinEntity = $searchCondition['fieldType'] . 'Fields';
        $joinAlias = $joinEntity . $index;
        $qb->join('search.' . $joinEntity, $joinAlias);
        if ($searchCondition['type'] == 'and') {
            $qb->andWhere($this->createNonTextQuery($joinAlias, $index));
        } else {
            $qb->orWhere($this->createNonTextQuery($joinAlias, $index));
        }
        $qb->setParameter('field' . $index, $searchCondition['fieldName'])
            ->setParameter('value' . $index, $searchCondition['fieldValue']);
    }

    /**
     * Create search string for non string parameters
     *
     * @param $joinAlias
     * @param $index
     *
     * @return string
     */
    protected function createNonTextQuery($joinAlias, $index)
    {
        return $joinAlias . '.field= :field' . $index . ' AND ' . $joinAlias . '.value = :value' . $index;
    }

    /**
     * @param \Oro\Bundle\SearchBundle\Query\Query $query
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getRequestQB(Query $query)
    {
        $qb = $this->createQueryBuilder('search');
        $useFrom = true;
        foreach ($query->getFrom() as $from) {
            if ($from == '*') {
                $useFrom = false;
            }
        }
        if ($useFrom) {
            $qb->andWhere($qb->expr()->in('search.entity', $query->getFrom()));
        }

        foreach ($query->getOptions() as $index => $searchCondition) {
            if ($searchCondition['fieldType'] == 'text') {
                $this->addTextField($qb, $index, $searchCondition);
            } else {
                $this->addNonTextField($qb, $index, $searchCondition);
            }
        }

        return $qb;
    }
}
