<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use OroCRM\Bundle\ChannelBundle\Entity\Repository\ChannelRepository;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\CartRepository;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\CustomerRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use OroCRM\Bundle\MagentoBundle\Entity\Repository\OrderRepository;

class MagentoBigNumberProvider
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var BigNumberDateHelper */
    protected $dateHelper;

    /**
     * @param RegistryInterface   $doctrine
     * @param AclHelper           $aclHelper
     * @param BigNumberDateHelper $dateHelper
     */
    public function __construct(
        RegistryInterface $doctrine,
        AclHelper $aclHelper,
        BigNumberDateHelper $dateHelper
    ) {
        $this->doctrine   = $doctrine;
        $this->aclHelper  = $aclHelper;
        $this->dateHelper = $dateHelper;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getRevenueValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Order', 'createdAt');

        $qb = $this->getOrderRepository()->getRevenueValueQB();
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
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ? : 0;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getOrdersNumberValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Order', 'createdAt');
        $qb = $this->getOrderRepository()->getOrdersNumberValueQB();
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
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ? : 0;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getAOVValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Order', 'createdAt');

        $qb = $this->getOrderRepository()->getAOVValueQB();
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
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['revenue'] ? $value['revenue'] / $value['ordersCount'] : 0;
    }

    /**
     * @param array $dateRange
     *
     * @return float
     */
    public function getDiscountedOrdersPercentValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Order', 'createdAt');
        $qb = $this->getOrderRepository()->getDiscountedOrdersPercentQB();
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
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['allOrders'] ? $value['discounted'] / $value['allOrders'] : 0;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getNewCustomersCountValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Customer', 'createdAt');
        $qb = $this->getCustomerRepository()->getNewCustomersNumberWhoMadeOrderQB();
        if ($start) {
            $qb
                ->andWhere('orders.createdAt > :start')
                ->andWhere('customer.createdAt > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $qb
                ->andWhere('orders.createdAt < :end')
                ->andWhere('customer.createdAt < :end')
                ->setParameter('end', $end);
        }
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ? : 0;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getReturningCustomersCountValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Customer', 'createdAt');
        $qb = $this->getCustomerRepository()->getReturningCustomersWhoMadeOrderQB();
        if ($start) {
            $qb
                ->andWhere('customer.createdAt < :start')
                ->andWhere('orders.createdAt > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $qb
                ->andWhere('orders.createdAt < :end')
                ->setParameter('end', $end);
        }
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ? : 0;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getAbandonedRevenueValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Cart', 'createdAt');

        $qb = $this->getCartRepository()->getAbandonedRevenueQB();
        if ($start) {
            $qb
                ->andWhere('cart.createdAt > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $qb
                ->andWhere('cart.createdAt < :end')
                ->setParameter('end', $end);
        }
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();

        return $value['val'] ? : 0;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getAbandonedCountValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Cart', 'createdAt');
        
        $qb = $this->getCartRepository()->getAbandonedCountQB();
        $value = $this->aclHelper->apply($qb)->getOneOrNullResult();
        if ($start) {
            $qb
                ->andWhere('cart.createdAt > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $qb
                ->andWhere('cart.createdAt < :end')
                ->setParameter('end', $end);
        }
        return $value['val'] ? : 0;
    }

    /**
     * @param array $dateRange
     *
     * @return float|null
     */
    public function getAbandonRateValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Cart', 'createdAt');
        $qb = $this->getCartRepository()->getGrandTotalSumQB();
        if ($start) {
            $qb
                ->andWhere('cart.createdAt > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $qb
                ->andWhere('cart.createdAt < :end')
                ->setParameter('end', $end);
        }
        $allCards = $this->aclHelper->apply($qb)->getOneOrNullResult();
        $allCards = (int)$allCards['val'];
        $result = 0;
        if (0 !== $allCards) {
            $abandonedCartsCount = $this->getAbandonedCountValues($dateRange);
            $result = $abandonedCartsCount / $allCards;
        }

        return $result;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getSiteVisitsValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod(
            $dateRange,
            'OroTrackingBundle:TrackingVisit',
            'firstActionTime'
        );
        $visitsQb = $this->getChannelRepository()->getVisitsCountForChannelTypeQB(ChannelType::TYPE);
        if ($start) {
            $visitsQb
                ->andWhere('visit.firstActionTime > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $visitsQb
                ->andWhere('visit.firstActionTime < :end')
                ->setParameter('end', $end);
        }

        return (int)$this->aclHelper->apply($visitsQb)->getSingleScalarResult();
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getOrderConversionValues($dateRange)
    {
        $result = 0;

        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Order', 'createdAt');
        $visitsQb = $this->getChannelRepository()->getVisitsCountForChannelTypeQB(ChannelType::TYPE);
        if ($start) {
            $visitsQb
                ->andWhere('visit.firstActionTime > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $visitsQb
                ->andWhere('visit.firstActionTime < :end')
                ->setParameter('end', $end);
        }
        $visits = (int)$this->aclHelper->apply($visitsQb)->getSingleScalarResult();
        if ($visits != 0) {
            $ordersCount = $this->getOrdersNumberValues($dateRange);
            $result = $ordersCount / $visits;
        }

        return $result;
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getCustomerConversionValues($dateRange)
    {
        $result = 0;

        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Customer', 'createdAt');

        $visitsQb = $this->getChannelRepository()->getVisitsCountForChannelTypeQB(ChannelType::TYPE);
        if ($start) {
            $visitsQb
                ->andWhere('visit.firstActionTime > :start')
                ->setParameter('start', $start);
        }
        if ($end) {
            $visitsQb
                ->andWhere('visit.firstActionTime < :end')
                ->setParameter('end', $end);
        }
        $visits    = (int)$this->aclHelper->apply($visitsQb)->getSingleScalarResult();
        if ($visits !== 0) {
            $customers = $this->getNewCustomersCountValues($dateRange);
            $result = $customers / $visits;
        }

        return $result;
    }

    /**
     * @return OrderRepository
     */
    protected function getOrderRepository()
    {
        return $this->doctrine->getRepository('OroCRMMagentoBundle:Order');
    }

    /**
     * @return CustomerRepository
     */
    protected function getCustomerRepository()
    {
        return $this->doctrine->getRepository('OroCRMMagentoBundle:Customer');
    }

    /**
     * @return CartRepository
     */
    protected function getCartRepository()
    {
        return $this->doctrine->getRepository('OroCRMMagentoBundle:Cart');
    }

    /**
     * @return ChannelRepository
     */
    protected function getChannelRepository()
    {
        return $this->doctrine->getRepository('OroCRMChannelBundle:Channel');
    }
}
