<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\EntityBundle\Exception\InvalidEntityException;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;

class OrderRepository extends ChannelAwareEntityRepository
{
    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param AclHelper $aclHelper
     * @return int
     */
    public function getRevenueValueByPeriod(\DateTime $start = null, \DateTime $end = null, AclHelper $aclHelper)
    {
        $select = 'SUM(
             CASE WHEN orders.subtotalAmount IS NOT NULL THEN orders.subtotalAmount ELSE 0 END -
             CASE WHEN orders.discountAmount IS NOT NULL THEN ABS(orders.discountAmount) ELSE 0 END
             ) as val';
        $qb    = $this->createQueryBuilder('orders');
        $qb->select($select);
        if ($start) {
            $qb
                ->andWhere('orders.createdAt > :dateStart')
                ->setParameter('dateStart', $start);
        }
        if ($end) {
            $qb
                ->andWhere('orders.createdAt > :dateEnd')
                ->setParameter('dateEnd', $end);
        }
        $this->applyActiveChannelLimitation($qb);

        $value = $aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ? : 0;
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param AclHelper $aclHelper
     * @return int
     */
    public function getOrdersNumberValueByPeriod(\DateTime $start = null, \DateTime $end = null, AclHelper $aclHelper)
    {
        $qb    = $this->createQueryBuilder('o');
        $qb->select('count(o.id) as val');
        if ($start) {
            $qb
                ->andWhere('o.createdAt > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $qb
                ->andWhere('o.createdAt < :end')
                ->setParameter('end', $end);
        }
        $this->applyActiveChannelLimitation($qb);

        $value = $aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ? : 0;
    }

    /**
     * get Average Order Amount by given period
     *
     * @param \DateTime $start
     * @param \DateTime $end
     * @param AclHelper $aclHelper
     * @return int
     */
    public function getAOVValueByPeriod(\DateTime $start = null, \DateTime $end = null, AclHelper $aclHelper)
    {
        $select = 'SUM(
             CASE WHEN o.subtotalAmount IS NOT NULL THEN o.subtotalAmount ELSE 0 END -
             CASE WHEN o.discountAmount IS NOT NULL THEN ABS(o.discountAmount) ELSE 0 END
             ) as revenue,
             count(o.id) as ordersCount';
        $qb    = $this->createQueryBuilder('o');
        $qb->select($select);
        if ($start) {
            $qb
                ->andWhere('o.createdAt > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $qb
                ->andWhere('o.createdAt < :end')
                ->setParameter('end', $end);
        }
        $this->applyActiveChannelLimitation($qb);

        $value = $aclHelper->apply($qb)->getOneOrNullResult();

        return $value['revenue'] ? $value['revenue'] / $value['ordersCount'] : 0;
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param AclHelper $aclHelper
     * @return float|int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDiscountedOrdersPercentByDatePeriod(
        \DateTime $start = null,
        \DateTime $end = null,
        AclHelper $aclHelper
    ) {
        $qb = $this->createQueryBuilder('o');
        $qb->select(
            'COUNT(o.id) as allOrders',
            'SUM(CASE WHEN (o.discountAmount IS NOT NULL AND o.discountAmount <> 0) THEN 1 ELSE 0 END) as discounted'
        );
        if ($start) {
            $qb
                ->andWhere('o.createdAt > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $qb
                ->andWhere('o.createdAt < :end')
                ->setParameter('end', $end);
        }
        $this->applyActiveChannelLimitation($qb);

        $value = $aclHelper->apply($qb)->getOneOrNullResult();

        return $value['allOrders'] ? $value['discounted'] / $value['allOrders'] : 0;
    }

    /**
     * @param Cart|Customer $item
     * @param string        $field
     *
     * @return Cart|Customer|null $item
     * @throws InvalidEntityException
     */
    public function getLastPlacedOrderBy($item, $field)
    {
        if (!($item instanceof Cart) && !($item instanceof Customer)) {
            throw new InvalidEntityException();
        }
        $qb = $this->createQueryBuilder('o');
        $qb->where('o.' . $field . ' = :item');
        $qb->setParameter('item', $item);
        $qb->orderBy('o.updatedAt', 'DESC');
        $qb->setMaxResults(1);
        $this->applyActiveChannelLimitation($qb);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param AclHelper  $aclHelper
     * @param \DateTime  $dateFrom
     * @param \DateTime  $dateTo
     * @param DateHelper $dateHelper
     * @return array
     */
    public function getAverageOrderAmount(
        AclHelper $aclHelper,
        \DateTime $dateFrom,
        \DateTime $dateTo,
        DateHelper $dateHelper
    ) {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getEntityManager();
        $channels      = $entityManager->getRepository('OroCRMChannelBundle:Channel')
            ->getAvailableChannelNames($aclHelper, ChannelType::TYPE);

        // execute data query
        $queryBuilder = $this->createQueryBuilder('o');
        $selectClause = '
            IDENTITY(o.dataChannel) AS dataChannelId,
            AVG(
                CASE WHEN o.subtotalAmount IS NOT NULL THEN o.subtotalAmount ELSE 0 END -
                CASE WHEN o.discountAmount IS NOT NULL THEN ABS(o.discountAmount) ELSE 0 END
            ) as averageOrderAmount';

        $dates = $dateHelper->getDatePeriod($dateFrom, $dateTo);

        $queryBuilder->select($selectClause)
            ->andWhere($queryBuilder->expr()->between('o.createdAt', ':dateStart', ':dateEnd'))
            ->setParameter('dateStart', $dateFrom)
            ->setParameter('dateEnd', $dateTo)
            ->groupBy('dataChannelId');

        $this->applyActiveChannelLimitation($queryBuilder);
        $dateHelper->addDatePartsSelect($dateFrom, $dateTo, $queryBuilder, 'o.createdAt');
        $amountStatistics = $aclHelper->apply($queryBuilder)->getArrayResult();

        $items = [];
        foreach ($amountStatistics as $row) {
            $key         = $dateHelper->getKey($dateFrom, $dateTo, $row);
            $channelId   = (int)$row['dataChannelId'];
            $channelName = $channels[$channelId]['name'];

            if (!isset($items[$channelName])) {
                $items[$channelName] = $dates;
            }
            $items[$channelName][$key]['amount'] = (float)$row['averageOrderAmount'];
        }

        // restore default keys
        foreach ($items as $channelName => $item) {
            $items[$channelName] = array_values($item);
        }

        return $items;
    }

    /**
     * @param AclHelper $aclHelper,
     * @param DateHelper $dateHelper
     * @param \DateTime $from
     * @param \DateTime|null $to
     *
     * @return array
     */
    public function getOrdersOverTime(
        AclHelper $aclHelper,
        DateHelper $dateHelper,
        \DateTime $from,
        \DateTime $to = null
    ) {
        $from = clone $from;
        $to = clone $to;

        $qb = $this->createQueryBuilder('o')
            ->select('COUNT(o.id) AS cnt');

        $dateHelper->addDatePartsSelect($from, $to, $qb, 'o.createdAt');
        if ($to) {
            $qb->andWhere($qb->expr()->between('o.createdAt', ':from', ':to'))
                ->setParameter('to', $to);
        } else {
            $qb->andWhere('o.createdAt > :from');
        }
        $qb->setParameter('from', $from);
        $this->applyActiveChannelLimitation($qb);

        return $aclHelper->apply($qb)->getResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @param DateHelper $dateHelper
     * @param \DateTime $from
     * @param \DateTime|null $to
     *
     * @return array
     */
    public function getRevenueOverTime(
        AclHelper $aclHelper,
        DateHelper $dateHelper,
        \DateTime $from,
        \DateTime $to = null
    ) {
        $from = clone $from;
        $to = clone $to;

        $qb = $this->createQueryBuilder('o')
            ->select('SUM(
                    CASE WHEN o.subtotalAmount IS NOT NULL THEN o.subtotalAmount ELSE 0 END -
                    CASE WHEN o.discountAmount IS NOT NULL THEN ABS(o.discountAmount) ELSE 0 END
                ) AS amount');

        $dateHelper->addDatePartsSelect($from, $to, $qb, 'o.createdAt');

        if ($to) {
            $qb->andWhere($qb->expr()->between('o.createdAt', ':from', ':to'))
                ->setParameter('to', $to);
        } else {
            $qb->andWhere('o.createdAt > :from');
        }
        $qb->setParameter('from', $from);
        $this->applyActiveChannelLimitation($qb);

        return $aclHelper->apply($qb)->getResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return int
     */
    public function getUniqueBuyersCount(AclHelper $aclHelper, \DateTime $from = null, \DateTime $to = null)
    {
        $qb = $this->createQueryBuilder('o');

        try {
            $qb
                ->select('COUNT(DISTINCT o.customer) + SUM(CASE WHEN o.isGuest = true THEN 1 ELSE 0 END)');
            if ($from) {
                $qb
                    ->andWhere('o.createdAt > :from')
                    ->setParameter('from', $from);
            }
            if ($to) {
                $qb
                    ->andWhere('o.createdAt < :to')
                    ->setParameter('to', $to);
            }
            $this->applyActiveChannelLimitation($qb);

            return (int) $aclHelper->apply($qb)->getSingleScalarResult();
        } catch (NoResultException $ex) {
            return 0;
        }
    }

    /**
     * @param $alias
     *
     * @return QueryBuilder
     */
    public function getUniqueBuyersCountQB($alias)
    {
        $qb = $this->createQueryBuilder($alias)
            ->select(sprintf(
                    'COUNT(DISTINCT %s.customer) + SUM(CASE WHEN %s.isGuest = true THEN 1 ELSE 0 END)', 
                    $alias, 
                    $alias
                )
            );
        $this->applyActiveChannelLimitation($qb);
        
        return $qb;
    }
}
