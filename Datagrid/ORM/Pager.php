<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM;

use Sonata\DoctrineORMAdminBundle\Datagrid\Pager as BasePager;
use Oro\Bundle\GridBundle\Datagrid\PagerInterface;

class Pager extends BasePager implements PagerInterface
{
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
        /** @var $countQuery \Doctrine\ORM\QueryBuilder */
        $countQuery = clone $this->getQuery();

        if (count($this->getParameters()) > 0) {
            $countQuery->setParameters($this->getParameters());
        }

        $countQuery->select(
            sprintf('%s.%s', $countQuery->getRootAlias(), current($this->getCountColumn()))
        );

        return count($countQuery->getQuery()->getResult());
    }
}
