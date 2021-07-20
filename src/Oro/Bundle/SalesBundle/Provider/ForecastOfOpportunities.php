<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\DashboardBundle\Provider\Converters\FilterDateRangeConverter;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatterInterface;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Oro\Bundle\SalesBundle\Provider\Opportunity\ForecastProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Provide functionality to get opportunities data
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ForecastOfOpportunities
{
    /** @var NumberFormatter */
    protected $numberFormatter;

    /** @var DateTimeFormatterInterface */
    protected $dateTimeFormatter;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var DateHelper */
    protected $dateHelper;

    /** @var ForecastProvider */
    protected $provider;

    /** @var FilterDateRangeConverter */
    protected $filterDateRangeConverter;

    /** @var array */
    protected $moments = [];

    public function __construct(
        NumberFormatter $numberFormatter,
        DateTimeFormatterInterface $dateTimeFormatter,
        TranslatorInterface $translator,
        DateHelper $dateHelper,
        ForecastProvider $provider,
        FilterDateRangeConverter $filterDateRangeConverter
    ) {
        $this->numberFormatter          = $numberFormatter;
        $this->dateTimeFormatter        = $dateTimeFormatter;
        $this->translator               = $translator;
        $this->dateHelper               = $dateHelper;
        $this->provider                 = $provider;
        $this->filterDateRangeConverter = $filterDateRangeConverter;
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @param string          $dataKey
     * @param string          $dataType
     * @param bool            $lessIsBetter
     *
     * @return array
     */
    public function getForecastOfOpportunitiesValues(
        WidgetOptionBag $widgetOptions,
        $dataKey,
        $dataType,
        $lessIsBetter = false
    ) {
        $lessIsBetter = (bool)$lessIsBetter;
        $result       = [];

        $compareToDate   = $widgetOptions->get('compareToDate');
        $usePrevious     = !empty($compareToDate['useDate']);
        $dateData        = $this->prepareDateRange($widgetOptions->get('dateRange'), $usePrevious);
        $value           = $this->provider
            ->getForecastData($widgetOptions, $dateData['start'], $dateData['end'], null);
        $result['value'] = $this->formatValue($value[$dataKey], $dataType);
        if (!empty($dateData['prev_start'])
            && !empty($dateData['prev_end'])
            && !empty($dateData['prev_moment'])
        ) {
            $pastResult              = $this->provider->getForecastData(
                $widgetOptions,
                $dateData['prev_start'],
                $dateData['prev_end'],
                $dateData['prev_moment']
            );
            $result['deviation']     = $this->translator
                ->trans('oro.sales.dashboard.forecast_of_opportunities.no_changes');
            $result                  = $this->prepareData(
                $dataType,
                $lessIsBetter,
                $pastResult[$dataKey],
                $value[$dataKey] - $pastResult[$dataKey],
                $result
            );
            $result['previousRange'] = $this->filterDateRangeConverter->getViewValue([
                'start' => $dateData['prev_start'],
                'end'   => $dateData['prev_end'],
                'type'  => $widgetOptions->get('dateRange')['type']
            ]);
        }

        return $result;
    }

    /**
     * @param mixed  $value
     * @param string $type
     * @param bool   $isDeviant
     *
     * @return string
     */
    protected function formatValue($value, $type = '', $isDeviant = false)
    {
        $sign      = null;
        $precision = 2;

        if ($isDeviant) {
            if ($value !== 0) {
                $sign  = $value > 0 ? '+' : '&minus;';
                $value = abs($value);
            }
            $precision = 0;
        }

        if ($type === 'currency') {
            $formattedValue = $this->numberFormatter->formatCurrency($value);
        } elseif ($type === 'percent') {
            $value          = round(($value) * 100, $precision) / 100;
            $formattedValue = $this->numberFormatter->formatPercent($value);
        } else {
            $formattedValue = $this->numberFormatter->formatDecimal($value);
        }

        if ($sign) {
            $formattedValue = sprintf('%s%s', $sign, $formattedValue);
        }

        return $formattedValue;
    }

    /**
     * @param $dataType
     * @param $lessIsBetter
     * @param $pastResult
     * @param $deviation
     * @param $result
     *
     * @return array
     */
    protected function prepareData($dataType, $lessIsBetter, $pastResult, $deviation, $result)
    {
        if ($pastResult != 0 && $dataType !== 'percent') {
            if ($deviation != 0) {
                $deviationPercent     = $deviation / $pastResult;
                $result['deviation']  = sprintf(
                    '%s (%s)',
                    $this->formatValue($deviation, $dataType, true),
                    $this->formatValue($deviationPercent, 'percent', true)
                );
                $result['isPositive'] = $this->isPositive($lessIsBetter, $deviation);
            }
        } else {
            if (round(($deviation) * 100, 0) != 0) {
                $result['deviation']  = $this->formatValue($deviation, $dataType, true);
                $result['isPositive'] = $this->isPositive($lessIsBetter, $deviation);
            }
        }

        return $result;
    }

    /**
     * Get is positive value
     *
     * @param $lessIsBetter
     * @param $deviation
     *
     * @return bool
     */
    protected function isPositive($lessIsBetter, $deviation)
    {
        if (!$lessIsBetter) {
            $result = $deviation > 0;
        } else {
            $result = !($deviation > 0);
        }

        return $result;
    }

    /**
     * @param array $dateRange
     * @param bool  $usePrevious
     *
     * @return array
     */
    protected function prepareDateRange(array $dateRange, $usePrevious)
    {
        /** @var \DateTime $start */
        /** @var \DateTime $end */
        $start = $dateRange['start'];
        $end   = $dateRange['end'];
        $data  = [
            'start' => $start,
            'end'   => $end
        ];
        if ($usePrevious
            && !empty($dateRange['prev_start'])
            && !empty($dateRange['prev_end'])
        ) {
            $data = array_merge(
                $data,
                [
                    'prev_start'     => $dateRange['prev_start'],
                    'prev_end'       => $dateRange['prev_end'],
                    'prev_moment'    => $this->getMoment($dateRange, $start)
                ]
            );
        }

        return $data;
    }

    /**
     * @param array     $dateRange
     * @param \DateTime $start
     *
     * @return array
     */
    protected function getMoment(array $dateRange, \DateTime $start)
    {
        /** @var \DateTime $prevStart */
        /** @var \DateTime $prevEnd */
        $prevStart = $dateRange['prev_start'];
        $key = md5(serialize([$dateRange['prev_start'], $start]));
        if (!isset($this->moments[$key])) {
            // current moment
            $now        = $this->dateHelper->getCurrentDateTime();
            $diff       = $start->diff($now);
            /** @var \DateTime $prevMoment */
            $prevMoment = clone $prevStart;
            $prevMoment->add($diff);
            $this->moments[$key] = $prevMoment;
        }

        return $this->moments[$key];
    }
}
