<?php

namespace Oro\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Doctrine repository for MagentoBundle Customer entity.
 */
class CustomerRepository extends ChannelAwareEntityRepository
{
    /**
     * Calculates the lifetime value for the given customer
     *
     * @param Customer $customer
     *
     * @return float
     */
    public function calculateLifetimeValue(Customer $customer)
    {
        $qb = $this->getEntityManager()->getRepository('OroMagentoBundle:Order')
            ->createQueryBuilder('o');

        $qb
            ->select('SUM(
                CASE WHEN o.subtotalAmount IS NOT NULL THEN o.subtotalAmount ELSE 0 END -
                CASE WHEN o.discountAmount IS NOT NULL THEN ABS(o.discountAmount) ELSE 0 END
                )')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('o.customer', ':customer'),
                    $qb->expr()->neq($qb->expr()->lower('o.status'), ':status')
                )
            )
            ->setParameter('customer', $customer)
            ->setParameter('status', Order::STATUS_CANCELED);

        return (float)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param AclHelper $aclHelper
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getNewCustomersNumberWhoMadeOrderByPeriod(\DateTime $start, \DateTime $end, AclHelper $aclHelper)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('COUNT(customer.id) as val')
            ->from('OroMagentoBundle:Order', 'orders')
            ->join('orders.customer', 'customer')
            ->having('COUNT(orders.id) > 0')
            ->andWhere($qb->expr()->between('customer.createdAt', ':dateStart', ':dateEnd'))
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
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getReturningCustomersWhoMadeOrderByPeriod(\DateTime $start, \DateTime $end, AclHelper $aclHelper)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('COUNT(customer.id) as val')
            ->from('OroMagentoBundle:Order', 'orders')
            ->join('orders.customer', 'customer')
            ->having('COUNT(orders.id) > 0')
            ->andWhere('customer.createdAt < :dateStart')
            ->andWhere($qb->expr()->between('orders.createdAt', ':dateStart', ':dateEnd'))
            ->setParameter('dateStart', $start, Types::DATETIME_MUTABLE)
            ->setParameter('dateEnd', $end, Types::DATETIME_MUTABLE);
        $this->applyActiveChannelLimitation($qb);

        $value = $aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ?  : 0;
    }

    /**
     * Returns data grouped by created_at, data_channel_id
     *
     * @param AclHelper  $aclHelper
     * @param DateHelper $dateHelper
     * @param \DateTime  $dateFrom
     * @param \DateTime  $dateTo
     * @param array      $ids Filter by channel ids
     *
     * @return array
     */
    public function getGroupedByChannelArray(
        AclHelper $aclHelper,
        DateHelper $dateHelper,
        \DateTime $dateFrom,
        \DateTime $dateTo = null,
        $ids = []
    ) {
        $qb = $this->createQueryBuilder('c');
        $qb->select(
            'COUNT(c) as cnt',
            'IDENTITY(c.dataChannel) as channelId'
        );
        $dateHelper->addDatePartsSelect($dateFrom, $dateTo, $qb, 'c.createdAt');

        if ($dateTo) {
            $qb->andWhere($qb->expr()->between('c.createdAt', ':dateFrom', ':dateTo'))
                ->setParameter('dateTo', $dateTo, Types::DATETIME_MUTABLE);
        } else {
            $qb->andWhere('c.createdAt > :dateFrom');
        }

        $qb->setParameter('dateFrom', $dateFrom, Types::DATETIME_MUTABLE);
        $qb->addGroupBy('c.dataChannel');

        if ($ids) {
            $qb->andWhere($qb->expr()->in('c.dataChannel', ':channelIds'))
                ->setParameter('channelIds', $ids);
        }

        return $aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @param Customer $customer
     * @param string   $value
     */
    public function updateCustomerLifetimeValue(Customer $customer, $value)
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->update('OroMagentoBundle:Customer', 'c')
            ->set('c.lifetime', 'c.lifetime + :value')
            ->setParameter('value', $value)
            ->where('c.id = :id')
            ->setParameter('id', $customer->getId());

        $qb->getQuery()->execute();
    }

    /**
     * @return QueryBuilder
     */
    public function getNewCustomersNumberWhoMadeOrderQB()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('COUNT(customer.id) as val')
            ->from('OroMagentoBundle:Order', 'orders')
            ->join('orders.customer', 'customer')
            ->having('COUNT(orders.id) > 0');
        $this->applyActiveChannelLimitation($qb);

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function getReturningCustomersWhoMadeOrderQB()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('COUNT(customer.id) as val')
            ->from('OroMagentoBundle:Order', 'orders')
            ->join('orders.customer', 'customer')
            ->having('COUNT(orders.id) > 0');
        $this->applyActiveChannelLimitation($qb);

        return $qb;
    }

    /**
     * @param int[]|null $customerIds
     * @param int[]|null $integrationIds
     *
     * @return BufferedQueryResultIterator
     */
    public function getIteratorByIdsAndIntegrationIds($customerIds, $integrationIds)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->orderBy('c.id');

        if ($customerIds) {
            $qb->andWhere('c.id in (:customerIds)')
                ->setParameter('customerIds', $customerIds);
        }

        if ($integrationIds) {
            $qb->andWhere('c.channel in (:integrationIds)')
                ->setParameter('integrationIds', $integrationIds);
        }

        return new BufferedQueryResultIterator($qb->getQuery());
    }
}
