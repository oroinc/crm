<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use LogicException;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use OroCRM\Bundle\MagentoBundle\Provider\Formatter\BigNumberFormatter;
use OroCRM\Bundle\MagentoBundle\Provider\Helper\BigNumberDateHelper;

class BigNumber
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var BigNumberFormatter */
    protected $bigNumberFormatter;

    /** @var DateTimeFormatter */
    protected $dateTimeFormatter;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var BigNumberDateHelper */
    protected $dateHelper;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var object[] */
    protected $valueProviders = [];

    /**
     * @param RegistryInterface   $doctrine
     * @param BigNumberFormatter  $bigNumberFormatter
     * @param DateTimeFormatter   $dateTimeFormatter
     * @param AclHelper           $aclHelper
     * @param BigNumberDateHelper $dateHelper
     * @param TranslatorInterface $translator
     */
    public function __construct(
        RegistryInterface $doctrine,
        BigNumberFormatter $bigNumberFormatter,
        DateTimeFormatter $dateTimeFormatter,
        AclHelper $aclHelper,
        BigNumberDateHelper $dateHelper,
        TranslatorInterface $translator
    ) {
        $this->doctrine           = $doctrine;
        $this->bigNumberFormatter = $bigNumberFormatter;
        $this->dateTimeFormatter  = $dateTimeFormatter;
        $this->aclHelper          = $aclHelper;
        $this->dateHelper         = $dateHelper;
        $this->translator         = $translator;
        $this->valueProviders[]   = $this;
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @param                 $getterName
     * @param                 $dataType
     * @param bool            $lessIsBetter
     * @return array
     */
    public function getBigNumberValues(WidgetOptionBag $widgetOptions, $getterName, $dataType, $lessIsBetter = false)
    {
        $getter           = $this->getGetter($getterName);
        $lessIsBetter     = (bool)$lessIsBetter;
        $result           = [];
        $dateRange        = $widgetOptions->get('dateRange');
        $value            = call_user_func($getter, $dateRange);
        $result['value']  = $this->bigNumberFormatter->formatValue($value, $dataType);
        $previousInterval = $widgetOptions->get('usePreviousInterval', []);

        if (count($previousInterval)) {
            $pastResult = call_user_func($getter, $previousInterval);

            $result['deviation'] = $this->translator->trans('orocrm.magento.dashboard.e_commerce_statistic.no_changes');

            $deviation = $value - $pastResult;
            if ($pastResult != 0 && $dataType !== 'percent') {
                if ($deviation != 0) {
                    $deviationPercent    = $deviation / $pastResult;
                    $result['deviation'] = sprintf(
                        '%s (%s)',
                        $this->bigNumberFormatter->formatValue($deviation, $dataType, true),
                        $this->bigNumberFormatter->formatValue($deviationPercent, 'percent', true)
                    );
                    if (!$lessIsBetter) {
                        $result['isPositive'] = $deviation > 0;
                    } else {
                        $result['isPositive'] = !($deviation > 0);
                    }
                }
            } else {
                if (round(($deviation) * 100, 0) != 0) {
                    $result['deviation'] = $this->bigNumberFormatter->formatValue($deviation, $dataType, true);
                    if (!$lessIsBetter) {
                        $result['isPositive'] = $deviation > 0;
                    } else {
                        $result['isPositive'] = !($deviation > 0);
                    }
                }
            }

            $result['previousRange'] = sprintf(
                '%s - %s',
                $this->dateTimeFormatter->formatDate($previousInterval['start']),
                $this->dateTimeFormatter->formatDate($previousInterval['end'])
            );
        }

        return $result;
    }

    /**
     * @param string $getterName
     *
     * @return callable
     */
    protected function getGetter($getterName)
    {
        foreach ($this->valueProviders as $provider) {
            $callback = [$provider, $getterName];
            if (is_callable($callback)) {
                return $callback;
            }
        }

        throw new LogicException(sprintf('Getter "%s" was not found', $getterName));
    }

    /**
     * @param object $provider
     */
    public function addValueProvider($provider)
    {
        $this->valueProviders[] = $provider;
    }

    /**
     * @param array $dateRange
     * @return int
     */
    protected function getRevenueValues($dateRange)
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
    protected function getOrdersNumberValues($dateRange)
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
    protected function getAOVValues($dateRange)
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
    protected function getDiscountedOrdersPercentValues($dateRange)
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
    protected function getNewCustomersCountValues($dateRange)
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
    protected function getReturningCustomersCountValues($dateRange)
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
    protected function getAbandonedRevenueValues($dateRange)
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
    protected function getAbandonedCountValues($dateRange)
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
    protected function getAbandonRateValues($dateRange)
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
    protected function getSiteVisitsValues($dateRange)
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
    protected function getOrderConversionValues($dateRange)
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
    protected function getCustomerConversionValues($dateRange)
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

    /**
     * @param array $dateRange
     *
     * @return int
     */
    protected function getTotalServicePipelineAmount(array $dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMSalesBundle:Opportunity')
            ->getTotalServicePipelineAmount($this->aclHelper, $start, $end);
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    protected function getNewLeadsCount($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMSalesBundle:Lead', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMSalesBundle:Lead')
            ->getNewLeadsCount($this->aclHelper, $start, $end);
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    protected function getLeadsCount($dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMSalesBundle:Lead', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMSalesBundle:Lead')
            ->getLeadsCount($this->aclHelper, $start, $end);
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    protected function getNewOpportunitiesCount($dateRange)
    {
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMSalesBundle:Opportunity')
            ->getNewOpportunitiesCount($this->aclHelper, $start, $end);
    }

    /**
     * @param array $dateRange
     *
     * @return int
     */
    protected function getOpportunitiesCount(array $dateRange)
    {
        list($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMSalesBundle:Opportunity')
            ->getOpportunitiesCount($this->aclHelper, $start, $end);
    }

    /**
     * @param array $dateRange
     *
     * @return double
     */
    protected function getOpenWeightedPipelineAmount($dateRange)
    {
        list ($start, $end) = $this->dateHelper->getPeriod($dateRange, 'OroCRMSalesBundle:Opportunity', 'createdAt');

        return $this->doctrine
            ->getRepository('OroCRMSalesBundle:Opportunity')
            ->getOpenWeightedPipelineAmount($this->aclHelper, $start, $end);
    }
}
