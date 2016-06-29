<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;

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
        $qb = $this->getEntityManager()->getRepository('OroCRMMagentoBundle:Order')
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
            ->from('OroCRMMagentoBundle:Order', 'orders')
            ->join('orders.customer', 'customer')
            ->having('COUNT(orders.id) > 0')
            ->andWhere($qb->expr()->between('customer.createdAt', ':dateStart', ':dateEnd'))
            ->andWhere($qb->expr()->between('orders.createdAt', ':dateStart', ':dateEnd'))
            ->setParameter('dateStart', $start)
            ->setParameter('dateEnd', $end);
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
            ->from('OroCRMMagentoBundle:Order', 'orders')
            ->join('orders.customer', 'customer')
            ->having('COUNT(orders.id) > 0')
            ->andWhere('customer.createdAt < :dateStart')
            ->andWhere($qb->expr()->between('orders.createdAt', ':dateStart', ':dateEnd'))
            ->setParameter('dateStart', $start)
            ->setParameter('dateEnd', $end);
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
                ->setParameter('dateTo', $dateTo);
        } else {
            $qb->andWhere('c.createdAt > :dateFrom');
        }

        $qb->setParameter('dateFrom', $dateFrom);
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
            ->update('OroCRMMagentoBundle:Customer', 'c')
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
            ->from('OroCRMMagentoBundle:Order', 'orders')
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
            ->from('OroCRMMagentoBundle:Order', 'orders')
            ->join('orders.customer', 'customer')
            ->having('COUNT(orders.id) > 0');
        $this->applyActiveChannelLimitation($qb);

        return $qb;
    }
}
