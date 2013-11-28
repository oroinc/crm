<?php

namespace OroCRM\Bundle\ReportBundle\Entity\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;

class ReportRepository extends EntityRepository
{
    /**
     * @return QueryBuilder
     */
    public function getReportsQueryBuilder()
    {
        return $this->createQueryBuilder('reports')
            ->select('reports');
    }
}
