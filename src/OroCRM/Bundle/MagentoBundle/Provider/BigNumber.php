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
     * @return array
     */
    public function getRevenueValues(WidgetOptionBag $widgetOptions)
    {
        return $this->getBigNumberValues(
            $widgetOptions,
            [
                'repo'         => 'OroCRMMagentoBundle:Order',
                'method'       => 'getRevenueValueByPeriod',
                'dataType'     => 'currency',
                'lessIsBetter' => false
            ]
        );
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @return array
     */
    public function getOrdersNumberValues(WidgetOptionBag $widgetOptions)
    {
        return $this->getBigNumberValues(
            $widgetOptions,
            [
                'repo'         => 'OroCRMMagentoBundle:Order',
                'method'       => 'getOrdersNumberValueByPeriod',
                'dataType'     => 'integer',
                'lessIsBetter' => false
            ]
        );
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @return array
     */
    public function getAOVValues(WidgetOptionBag $widgetOptions)
    {
        return $this->getBigNumberValues(
            $widgetOptions,
            [
                'repo'         => 'OroCRMMagentoBundle:Order',
                'method'       => 'getAOVValueByPeriod',
                'dataType'     => 'currency',
                'lessIsBetter' => false
            ]
        );
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @return array
     */
    public function getDiscountedOrdersPercentValues(WidgetOptionBag $widgetOptions)
    {
        return $this->getBigNumberValues(
            $widgetOptions,
            [
                'repo'         => 'OroCRMMagentoBundle:Order',
                'method'       => 'getDiscountedOrdersPercentByDatePeriod',
                'dataType'     => 'percent',
                'lessIsBetter' => false
            ]
        );
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @return array
     */
    public function getNewCustomersCountValues(WidgetOptionBag $widgetOptions)
    {
        return $this->getBigNumberValues(
            $widgetOptions,
            [
                'repo'         => 'OroCRMMagentoBundle:Customer',
                'method'       => 'getNewCustomersNumberWhoMadeOrderByPeriod',
                'dataType'     => 'integer',
                'lessIsBetter' => false
            ]
        );
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @return array
     */
    public function getReturningCustomersCountValues(WidgetOptionBag $widgetOptions)
    {
        return $this->getBigNumberValues(
            $widgetOptions,
            [
                'repo'         => 'OroCRMMagentoBundle:Customer',
                'method'       => 'getReturningCustomersWhoMadeOrderByPeriod',
                'dataType'     => 'integer',
                'lessIsBetter' => false
            ]
        );
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @return array
     */
    public function getAbandonedRevenueValues(WidgetOptionBag $widgetOptions)
    {
        return $this->getBigNumberValues(
            $widgetOptions,
            [
                'repo'         => 'OroCRMMagentoBundle:Cart',
                'method'       => 'getAbandonedRevenueByPeriod',
                'dataType'     => 'currency',
                'lessIsBetter' => true
            ]
        );
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @return array
     */
    public function getAbandonedCountValues(WidgetOptionBag $widgetOptions)
    {
        return $this->getBigNumberValues(
            $widgetOptions,
            [
                'repo'         => 'OroCRMMagentoBundle:Cart',
                'method'       => 'getAbandonedCountByPeriod',
                'dataType'     => 'integer',
                'lessIsBetter' => true
            ]
        );
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @return array
     */
    public function getAbandonRateValues(WidgetOptionBag $widgetOptions)
    {
        return $this->getBigNumberValues(
            $widgetOptions,
            [
                'repo'         => 'OroCRMMagentoBundle:Cart',
                'method'       => 'getAbandonRateByPeriod',
                'dataType'     => 'percent',
                'lessIsBetter' => true
            ]
        );
    }

    /**
     * @param $widgetOptions
     * @param $parameters
     * @return mixed
     */
    protected function getBigNumberValues($widgetOptions, $parameters)
    {
        $result           = [];
        $repo             = $this->doctrine->getRepository($parameters['repo']);
        $dateRange        = $widgetOptions->get('dateRange');
        $from             = $dateRange['start'];
        $to               = $dateRange['end'];
        $value            = $repo->{$parameters['method']}($from, $to, $this->aclHelper);
        $result['value']  = $this->formatValue($value, $parameters['dataType']);
        $previousInterval = $widgetOptions->get('usePreviousInterval', []);

        if (count($previousInterval)) {
            $previousFrom = $previousInterval['start'];
            $previousTo   = $previousInterval['end'];
            $pastResult   = $repo->{$parameters['method']}($previousFrom, $previousTo, $this->aclHelper);

            $result['deviation'] = $this->translator->trans('orocrm.magento.dashboard.e_commerce_statistic.no_changes');

            $deviation = $value - $pastResult;
            if ($pastResult != 0 && $parameters['dataType'] !== 'percent') {
                if ($deviation != 0) {
                    $deviationPercent     = $deviation / $pastResult;
                    $result['deviation']  = sprintf(
                        '%s (%s)',
                        $this->formatValue($deviation, $parameters['dataType'], true),
                        $this->formatValue($deviationPercent, 'percent', true)
                    );
                    $result['isPositive'] = ($deviation >= 0 && !$parameters['lessIsBetter']);
                }
            } else {
                if (round(($deviation) * 100, 0) != 0) {
                    $result['deviation']  = $this->formatValue($deviation, $parameters['dataType'], true);
                    $result['isPositive'] = ($deviation >= 0 && !$parameters['lessIsBetter']);
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