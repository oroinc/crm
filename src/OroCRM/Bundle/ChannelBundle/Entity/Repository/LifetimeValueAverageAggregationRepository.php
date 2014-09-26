<?php

namespace OroCRM\Bundle\ChannelBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class LifetimeValueAverageAggregationRepository extends EntityRepository
{
    public function findAmountStatisticsByDate($date)
    {
        /** @var QueryBuilder */
        $qb = $this->createQueryBuilder('dl');

        $qb->select('(dl.dataChannel) as dataChannel, dl.createdAt as createdAt, dl.month as month, dl.year as year');
        $qb->addSelect($qb->expr()->max('dl.amount') . ' as amount');
        $qb->andWhere('dl.createdAt > :date');
        $qb->addGroupBy('dl.year', 'dl.month');
        $qb->setParameter('date', $date);

        return $qb->getQuery()->getResult();
    }
}
