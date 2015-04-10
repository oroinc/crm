<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\TranslationBundle\Translation\Translator;

class BigNumber
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var NumberFormatter */
    protected $numberFormatter;

    /** @var DateTimeFormatter */
    protected $dateTimeFormatter;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var Translator */
    protected $translator;

    /**
     * @param RegistryInterface $doctrine
     * @param NumberFormatter   $numberFormatter
     * @param DateTimeFormatter $dateTimeFormatter
     * @param AclHelper         $aclHelper
     * @param Translator        $translator
     */
    public function __construct(
        RegistryInterface $doctrine,
        NumberFormatter $numberFormatter,
        DateTimeFormatter $dateTimeFormatter,
        AclHelper $aclHelper,
        Translator $translator
    ) {
        $this->doctrine          = $doctrine;
        $this->numberFormatter   = $numberFormatter;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->aclHelper         = $aclHelper;
        $this->translator        = $translator;
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
        $lessIsBetter     = (bool)$lessIsBetter;
        $result           = [];
        $dateRange        = $widgetOptions->get('dateRange');
        $from             = $dateRange['start'];
        $to               = $dateRange['end'];
        $value            = $this->{$getterName}($from, $to);
        $result['value']  = $this->formatValue($value, $dataType);
        $previousInterval = $widgetOptions->get('usePreviousInterval', []);

        if (count($previousInterval)) {
            $previousFrom = $previousInterval['start'];
            $previousTo   = $previousInterval['end'];
            $pastResult   = $this->{$getterName}($previousFrom, $previousTo);

            $result['deviation'] = $this->translator->trans('orocrm.magento.dashboard.e_commerce_statistic.no_changes');

            $deviation = $value - $pastResult;
            if ($pastResult != 0 && $dataType !== 'percent') {
                if ($deviation != 0) {
                    $deviationPercent     = $deviation / $pastResult;
                    $result['deviation']  = sprintf(
                        '%s (%s)',
                        $this->formatValue($deviation, $dataType, true),
                        $this->formatValue($deviationPercent, 'percent', true)
                    );
                    if (!$lessIsBetter) {
                        $result['isPositive'] = $deviation > 0;
                    } else {
                        $result['isPositive'] = !($deviation > 0);
                    }
                }
            } else {
                if (round(($deviation) * 100, 0) != 0) {
                    $result['deviation']  = $this->formatValue($deviation, $dataType, true);
                    if (!$lessIsBetter) {
                        $result['isPositive'] = $deviation > 0;
                    } else {
                        $result['isPositive'] = !($deviation > 0);
                    }
                }
            }

            $result['previousRange'] = sprintf(
                '%s - %s',
                $this->dateTimeFormatter->formatDate($previousFrom),
                $this->dateTimeFormatter->formatDate($previousTo)
            );
        }

        return $result;
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return int
     */
    protected function getRevenueValues(\DateTime $start, \DateTime $end)
    {
        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Order')
            ->getRevenueValueByPeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return int
     */
    protected function getOrdersNumberValues(\DateTime $start, \DateTime $end)
    {
        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Order')
            ->getOrdersNumberValueByPeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return int
     */
    protected function getAOVValues(\DateTime $start, \DateTime $end)
    {
        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Order')
            ->getAOVValueByPeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return float
     */
    protected function getDiscountedOrdersPercentValues(\DateTime $start, \DateTime $end)
    {
        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Order')
            ->getDiscountedOrdersPercentByDatePeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return int
     */
    protected function getNewCustomersCountValues(\DateTime $start, \DateTime $end)
    {
        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Customer')
            ->getNewCustomersNumberWhoMadeOrderByPeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return int
     */
    protected function getReturningCustomersCountValues(\DateTime $start, \DateTime $end)
    {
        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Customer')
            ->getReturningCustomersWhoMadeOrderByPeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return int
     */
    protected function getAbandonedRevenueValues(\DateTime $start, \DateTime $end)
    {
        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Cart')
            ->getAbandonedRevenueByPeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return int
     */
    protected function getAbandonedCountValues(\DateTime $start, \DateTime $end)
    {
        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Cart')
            ->getAbandonedCountByPeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return float|null
     */
    protected function getAbandonRateValues(\DateTime $start, \DateTime $end)
    {
        return $this->doctrine
            ->getRepository('OroCRMMagentoBundle:Cart')
            ->getAbandonRateByPeriod($start, $end, $this->aclHelper);
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return int
     */
    protected function getSiteVisitsValues(\DateTime $start, \DateTime $end)
    {
        return $this->doctrine
            ->getRepository('OroCRMChannelBundle:Channel')
            ->getVisitsCountByPeriodForChannelType($start, $end, $this->aclHelper, 'magento');
    }

    /**
     * @param mixed  $value
     * @param string $type
     * @return string
     */
    protected function formatValue($value, $type = '', $isDeviant = false)
    {
        $sign = null;

        if ($isDeviant && $value !== 0) {
            $sign  = $value > 0 ? '+' : '&minus;';
            $value = abs($value);
        }
        switch ($type) {
            case 'currency':
                $value = $this->numberFormatter->formatCurrency($value);
                break;
            case 'percent':
                if ($isDeviant) {
                    $value = round(($value) * 100, 0) / 100;
                } else {
                    $value = round(($value) * 100, 2) / 100;
                }

                $value = $this->numberFormatter->formatPercent($value);
                break;
            default:
                $value = $this->numberFormatter->formatDecimal($value);
        }

        return $isDeviant && !is_null($sign) ? sprintf('%s%s', $sign, $value) : $value;
    }
}