<?php

namespace OroCRM\Bundle\ChannelBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class DatedLifetimeValueRepository extends EntityRepository
{
    public function findAmountStatisticsByDate($date)
    {
        /** @var QueryBuilder */
        $qb = $this->createQueryBuilder('dl');
        $qb->andWhere('dl.createdAt > :date');
        $qb->setParameter('date', $date);

        return $qb->getQuery()->getResult();
    }
}
