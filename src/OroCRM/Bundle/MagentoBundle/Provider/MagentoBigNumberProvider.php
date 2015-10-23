<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\DashboardBundle\Provider\BigNumber\BigNumberDateHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

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
     * @return int
     */
    public function getRevenueValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Order', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Order')
            ->getRevenueValueByPeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param array $dateRange
     * @return int
     */
    public function getOrdersNumberValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Order', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Order')
            ->getOrdersNumberValueByPeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param array $dateRange
     * @return int
     */
    public function getAOVValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Order', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Order')
            ->getAOVValueByPeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param array $dateRange
     * @return float
     */
    public function getDiscountedOrdersPercentValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Order', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Order')
            ->getDiscountedOrdersPercentByDatePeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param array $dateRange
     * @return int
     */
    public function getNewCustomersCountValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Customer', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Customer')
            ->getNewCustomersNumberWhoMadeOrderByPeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param array $dateRange
     * @return int
     */
    public function getReturningCustomersCountValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Customer', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Customer')
            ->getReturningCustomersWhoMadeOrderByPeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param array $dateRange
     * @return int
     */
    public function getAbandonedRevenueValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Cart', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Cart')
            ->getAbandonedRevenueByPeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param array $dateRange
     * @return int
     */
    public function getAbandonedCountValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Cart', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Cart')
            ->getAbandonedCountByPeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param array $dateRange
     * @return float|null
     */
    public function getAbandonRateValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Cart', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Cart')
            ->getAbandonRateByPeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param array $dateRange
     * @return int
     */
    public function getSiteVisitsValues($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod(
            $dateRange,
            'OroTrackingBundle:TrackingVisit',
            'firstActionTime'
        );

        return $this->doctrine
            ->getRepository('OroCRMChannelBundle:Channel')
            ->getVisitsCountByPeriodForChannelType($start, $end, $this->aclHelper, ChannelType::TYPE);
    }

    /**
     * @param array $dateRange
     * @return int
     */
    public function getOrderConversionValues($dateRange)
    {
        $result = 0;

        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Order', 'createdAt');

        $ordersCount = $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Order')
            ->getOrdersNumberValueByPeriod($start, $end, $this->aclHelper);
        $visits      = $this->doctrine
            ->getRepository('OroCRMChannelBundle:Channel')
            ->getVisitsCountByPeriodForChannelType($start, $end, $this->aclHelper, ChannelType::TYPE);
        if ($visits != 0) {
            $result = $ordersCount / $visits;
        }

        return $result;
    }

    /**
     * @param array $dateRange
     * @return int
     */
    public function getCustomerConversionValues($dateRange)
    {
        $result = 0;

        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Customer', 'createdAt');

        $customers = $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Customer')
            ->getNewCustomersNumberWhoMadeOrderByPeriod($start, $end, $this->aclHelper);
        $visits    = $this->doctrine
            ->getRepository('OroCRMChannelBundle:Channel')
            ->getVisitsCountByPeriodForChannelType($start, $end, $this->aclHelper, ChannelType::TYPE);
        if ($visits !== 0) {
            $result = $customers / $visits;
        }

        return $result;
    }
}
