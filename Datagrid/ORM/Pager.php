<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM;

use Sonata\DoctrineORMAdminBundle\Datagrid\Pager as BasePager;
use Oro\Bundle\GridBundle\Datagrid\PagerInterface;

class Pager extends BasePager implements PagerInterface
{
    /**
     * List of additional fields which must be used to calculate number of records
     *
     * @var array
     */
    protected $complexFields = array();

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        return intval(parent::getNbResults());
    }

    /**
     * {@inheritdoc}
     */
    public function computeNbResult()
    {
        return count($this->getUniqueIds());
    }

    /**
     * @return array
     */
    public function getUniqueIds()
    {
        /** @var $countQuery \Doctrine\ORM\QueryBuilder */
        $countQuery = clone $this->getQuery();

        if (count($this->getParameters()) > 0) {
            $countQuery->setParameters($this->getParameters());
        }

        $countQuery->resetDQLPart('orderBy');

        $selectParts = array();
        $selectParts[] = sprintf('%s.%s', $countQuery->getRootAlias(), current($this->getCountColumn()));
        $selectParts = array_merge($selectParts, $this->complexFields);

        $countQuery->select('DISTINCT ' . implode(', ', $selectParts));

        $ids = array();
        $results = $countQuery->getQuery()->getArrayResult();
        foreach ($results as $result) {
            $ids[] = reset($result);
        }

        return $ids;
    }

    /**
     * @param array $complexFields
     */
    public function setComplexFields($complexFields)
    {
        $this->complexFields = $complexFields;
    }
}
