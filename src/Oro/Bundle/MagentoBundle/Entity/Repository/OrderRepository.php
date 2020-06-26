<?php

namespace Oro\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Oro\Bundle\EntityBundle\Exception\InvalidEntityException;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

/**
 * Doctrine repository for MagentoBundle Order entity.
 */
class OrderRepository extends ChannelAwareEntityRepository
{
    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param AclHelper $aclHelper
     *
     * @return int
     */
    public function getRevenueValueByPeriod(\DateTime $start, \DateTime $end, AclHelper $aclHelper)
    {
        $select = 'SUM(
             CASE WHEN orders.subtotalAmount IS NOT NULL THEN orders.subtotalAmount ELSE 0 END -
             CASE WHEN orders.discountAmount IS NOT NULL THEN ABS(orders.discountAmount) ELSE 0 END
             ) as val';
        $qb    = $this->createQueryBuilder('orders');
        $qb->select($select)
            ->andWhere($qb->expr()->between('orders.createdAt', ':dateStart', ':dateEnd'))
            ->setParameter('dateStart', $start, Types::DATETIME_MUTABLE)
            ->setParameter('dateEnd', $end, Types::DATETIME_MUTABLE);
        $this->applyActiveChannelLimitation($qb);

        $value = $aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ? : 0;
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param AclHelper $aclHelper
     *
     * @return int
     */
    public function getOrdersNumberValueByPeriod(\DateTime $start, \DateTime $end, AclHelper $aclHelper)
    {
        $qb    = $this->createQueryBuilder('o');
        $qb->select('COUNT(o.id) as val')
            ->andWhere($qb->expr()->between('o.createdAt', ':dateStart', ':dateEnd'))
            ->setParameter('dateStart', $start, Types::DATETIME_MUTABLE)
            ->setParameter('dateEnd', $end, Types::DATETIME_MUTABLE);
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
     *
     * @return int
     */
    public function getAOVValueByPeriod(\DateTime $start, \DateTime $end, AclHelper $aclHelper)
    {
        $select = 'SUM(
             CASE WHEN o.subtotalAmount IS NOT NULL THEN o.subtotalAmount ELSE 0 END -
             CASE WHEN o.discountAmount IS NOT NULL THEN ABS(o.discountAmount) ELSE 0 END
             ) as revenue,
             count(o.id) as ordersCount';
        $qb    = $this->createQueryBuilder('o');
        $qb->select($select)
            ->andWhere($qb->expr()->between('o.createdAt', ':dateStart', ':dateEnd'))
            ->setParameter('dateStart', $start, Types::DATETIME_MUTABLE)
            ->setParameter('dateEnd', $end, Types::DATETIME_MUTABLE);
        $this->applyActiveChannelLimitation($qb);

        $value = $aclHelper->apply($qb)->getOneOrNullResult();

        return $value['revenue'] ? $value['revenue'] / $value['ordersCount'] : 0;
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param AclHelper $aclHelper
     *
     * @return float|int
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDiscountedOrdersPercentByDatePeriod(
        \DateTime $start,
        \DateTime $end,
        AclHelper $aclHelper
    ) {
        $qb = $this->createQueryBuilder('o');
        $qb->select(
            'COUNT(o.id) as allOrders',
            'SUM(CASE WHEN (o.discountAmount IS NOT NULL AND o.discountAmount <> 0) THEN 1 ELSE 0 END) as discounted'
        );
        $qb->andWhere($qb->expr()->between('o.createdAt', ':dateStart', ':dateEnd'));
        $qb->setParameter('dateStart', $start, Types::DATETIME_MUTABLE);
        $qb->setParameter('dateEnd', $end, Types::DATETIME_MUTABLE);
        $this->applyActiveChannelLimitation($qb);

        $value = $aclHelper->apply($qb)->getOneOrNullResult();
        return $value['allOrders'] ? $value['discounted'] / $value['allOrders'] : 0;
    }

    /**
     * @param Cart|Customer $item
     * @param string        $field
     *
     * @return Cart|Customer|null $item
     *
     * @throws InvalidEntityException
     */
    public function getLastPlacedOrderBy($item, $field)
    {
        if (!($item instanceof Cart) && !($item instanceof Customer)) {
            throw new InvalidEntityException();
        }
        $qb = $this->createQueryBuilder('o');
        $qb->where($qb->expr()->eq(QueryBuilderUtil::getField('o', $field), ':item'));
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
     *
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
        /**
         * Remove dependency on exact magento channel type in CRM-8154
         */
        $channels      = $entityManager
            ->getRepository('OroChannelBundle:Channel')
            ->getAvailableChannelNames($aclHelper, MagentoChannelType::TYPE);

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
            ->setParameter('dateStart', $dateFrom, Types::DATETIME_MUTABLE)
            ->setParameter('dateEnd', $dateTo, Types::DATETIME_MUTABLE)
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
     * @param AclHelper      $aclHelper ,
     * @param DateHelper     $dateHelper
     * @param \DateTime      $from
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
        $to   = clone $to;

        $qb = $this->createQueryBuilder('o')
            ->select('COUNT(o.id) AS cnt');

        $dateHelper->addDatePartsSelect($from, $to, $qb, 'o.createdAt');
        if ($to) {
            $qb->andWhere($qb->expr()->between('o.createdAt', ':from', ':to'))
                ->setParameter('to', $to, Types::DATETIME_MUTABLE);
        } else {
            $qb->andWhere('o.createdAt > :from');
        }
        $qb->setParameter('from', $from, Types::DATETIME_MUTABLE);
        $this->applyActiveChannelLimitation($qb);

        return $aclHelper->apply($qb)->getResult();
    }

    /**
     * @param AclHelper      $aclHelper
     * @param DateHelper     $dateHelper
     * @param \DateTime      $from
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
        $to   = clone $to;

        $qb = $this->createQueryBuilder('o')
            ->select('SUM(
                    CASE WHEN o.subtotalAmount IS NOT NULL THEN o.subtotalAmount ELSE 0 END -
                    CASE WHEN o.discountAmount IS NOT NULL THEN ABS(o.discountAmount) ELSE 0 END
                ) AS amount');

        $dateHelper->addDatePartsSelect($from, $to, $qb, 'o.createdAt');

        if ($to) {
            $qb->andWhere($qb->expr()->between('o.createdAt', ':from', ':to'))
                ->setParameter('to', $to, Types::DATETIME_MUTABLE);
        } else {
            $qb->andWhere('o.createdAt > :from');
        }
        $qb->setParameter('from', $from, Types::DATETIME_MUTABLE);
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
            $qb->select('COUNT(DISTINCT o.customer) + SUM(CASE WHEN o.isGuest = true THEN 1 ELSE 0 END)');
            if ($from) {
                $qb
                    ->andWhere('o.createdAt > :from')
                    ->setParameter('from', $from, Types::DATETIME_MUTABLE);
            }
            if ($to) {
                $qb
                    ->andWhere('o.createdAt > :to')
                    ->setParameter('to', $to, Types::DATETIME_MUTABLE);
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
    public function getUniqueCustomersCountQB($alias)
    {
        $qb = $this->createQueryBuilder($alias)
            ->select(
                QueryBuilderUtil::sprintf(
                    'COUNT(DISTINCT %s.customer) + SUM(CASE WHEN %s.isGuest = true THEN 1 ELSE 0 END)',
                    $alias,
                    $alias
                )
            );
        $this->applyActiveChannelLimitation($qb);

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function getRevenueValueQB()
    {
        $select = 'SUM(
             CASE WHEN orders.subtotalAmount IS NOT NULL THEN orders.subtotalAmount ELSE 0 END -
             CASE WHEN orders.discountAmount IS NOT NULL THEN ABS(orders.discountAmount) ELSE 0 END
             ) as val';
        $qb     = $this->createQueryBuilder('orders');
        $qb->select($select);

        $this->applyActiveChannelLimitation($qb);

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function getOrdersNumberValueQB()
    {
        $qb = $this->createQueryBuilder('o');
        $qb->select('COUNT(o.id) as val');

        $this->applyActiveChannelLimitation($qb);

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function getAOVValueQB()
    {
        $select = 'SUM(
             CASE WHEN o.subtotalAmount IS NOT NULL THEN o.subtotalAmount ELSE 0 END -
             CASE WHEN o.discountAmount IS NOT NULL THEN ABS(o.discountAmount) ELSE 0 END
             ) as revenue,
             count(o.id) as ordersCount';
        $qb     = $this->createQueryBuilder('o');
        $qb->select($select);
        $this->applyActiveChannelLimitation($qb);

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function getDiscountedOrdersPercentQB()
    {
        $qb = $this->createQueryBuilder('o');
        $qb->select(
            'COUNT(o.id) as allOrders',
            'SUM(CASE WHEN (o.discountAmount IS NOT NULL AND o.discountAmount <> 0) THEN 1 ELSE 0 END) as discounted'
        );
        $this->applyActiveChannelLimitation($qb);

        return $qb;
    }
}
