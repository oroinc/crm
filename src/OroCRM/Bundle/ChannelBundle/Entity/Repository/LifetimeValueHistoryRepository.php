<?php

namespace OroCRM\Bundle\ChannelBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class LifetimeValueHistoryRepository extends EntityRepository
{
    public function getMaxAndMinDate()
    {
        /** @var QueryBuilder */
        $qb = $this->createQueryBuilder('lt');

        $qb->select($qb->expr()->min('lt.createdAt') . 'as minDate');
        $qb->addSelect($qb->expr()->max('lt.createdAt') . 'as maxDate');

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getAverageAmount()
    {
        /** @var QueryBuilder */
        $qb = $this->createQueryBuilder('lt');

        $qb->addSelect('(lt.dataChannel) as dataChannel');
        $qb->addSelect($qb->expr()->avg('lt.amount') . ' as avgAmount');
        $qb->andWhere('lt.status = 1');

        return $qb->getQuery()->getResult();
    }
}
