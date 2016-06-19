<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Component\DoctrineUtils\ORM\QueryUtils;
use Oro\Bundle\DashboardBundle\Helper\DateHelper;

/**
 * Class ForecastOfOpportunities
 * @package OroCRM\Bundle\SalesBundle\Provider
 */
class ForecastOfOpportunities
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var NumberFormatter */
    protected $numberFormatter;

    /** @var DateTimeFormatter */
    protected $dateTimeFormatter;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var DateHelper */
    protected $dateHelper;

    /** @var  array */
    protected $ownersValues;

    /** @var array */
    protected $ownerIds = [];

    /**
     * @param RegistryInterface   $doctrine
     * @param NumberFormatter     $numberFormatter
     * @param DateTimeFormatter   $dateTimeFormatter
     * @param AclHelper           $aclHelper
     * @param TranslatorInterface $translator
     * @param DateHelper          $dateHelper
     */
    public function __construct(
        RegistryInterface $doctrine,
        NumberFormatter $numberFormatter,
        DateTimeFormatter $dateTimeFormatter,
        AclHelper $aclHelper,
        TranslatorInterface $translator,
        DateHelper $dateHelper
    ) {
        $this->doctrine          = $doctrine;
        $this->numberFormatter   = $numberFormatter;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->aclHelper         = $aclHelper;
        $this->translator        = $translator;
        $this->dateHelper        = $dateHelper;
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @param string          $getterName
     * @param string          $dataType
     * @param bool            $lessIsBetter
     *
     * @return array
     */
    public function getForecastOfOpportunitiesValues(
        WidgetOptionBag $widgetOptions,
        $getterName,
        $dataType,
        $lessIsBetter = false
    ) {
        $lessIsBetter = (bool)$lessIsBetter;
        $result       = [];

        $ownerIds        = $this->getOwnerIds($widgetOptions);
        $compareToDate   = $widgetOptions->get('compareToDate');
        $usePrevious     = !empty($compareToDate['useDate']);
        $dateData        = $this->prepareDateRange($widgetOptions->get('dateRange'), $usePrevious);
        $value           = $this->{$getterName}($ownerIds, $dateData['start'], $dateData['end']);
        $result['value'] = $this->formatValue($value, $dataType);
        if (!empty($dateData['prev_start'])
            && !empty($dateData['prev_end'])
            && !empty($dateData['prev_moment'])
        ) {
            $pastResult              = $this->{$getterName}(
                $ownerIds,
                $dateData['prev_start'],
                $dateData['prev_end'],
                $dateData['prev_moment']
            );
            $result['deviation']     = $this->translator
                ->trans('orocrm.sales.dashboard.forecast_of_opportunities.no_changes');
            $result                  = $this->prepareData(
                $dataType,
                $lessIsBetter,
                $pastResult,
                $value - $pastResult,
                $result
            );
            $result['previousRange'] = $this->dateTimeFormatter->formatDate($dateData['prev_moment']);
        }

        return $result;
    }

    /**
     * @param array                $ownerIds
     * @param \DateTime            $start
     * @param \DateTime            $end
     * @param \DateTime|string|int $compareToDate
     *
     * @return int
     */
    protected function getInProgressValues($ownerIds, $start = null, $end = null, $compareToDate = null)
    {
        $values = $this->getOwnersValues($ownerIds, $start, $end, $compareToDate);

        return $values && isset($values['inProgressCount']) ? $values['inProgressCount'] : 0;
    }

    /**
     * @param array                $ownerIds
     * @param \DateTime            $start
     * @param \DateTime            $end
     * @param \DateTime|string|int $compareToDate
     *
     * @return int
     */
    protected function getTotalForecastValues($ownerIds, $start = null, $end = null, $compareToDate = null)
    {
        $values = $this->getOwnersValues($ownerIds, $start, $end, $compareToDate);

        return $values && isset($values['budgetAmount']) ? $values['budgetAmount'] : 0;
    }

    /**
     * @param array                $ownerIds
     * @param \DateTime            $start
     * @param \DateTime            $end
     * @param \DateTime|string|int $compareToDate
     *
     * @return int
     */
    protected function getWeightedForecastValues($ownerIds, $start = null, $end = null, $compareToDate = null)
    {
        $values = $this->getOwnersValues($ownerIds, $start, $end, $compareToDate);

        return $values && isset($values['weightedForecast']) ? $values['weightedForecast'] : 0;
    }

    /**
     * @param array                $ownerIds
     * @param \DateTime            $start
     * @param \DateTime            $end
     * @param \DateTime|string|int $date
     *
     * @return mixed
     */
    protected function getOwnersValues(array $ownerIds, $start = null, $end = null, $date = null)
    {
        $dateKey      = $date ? $this->dateTimeFormatter->formatDate($date) : '';
        $startDateKey = $start ? : '';
        $endDateKey   = $end ? : '';
        $key          = sha1(
            implode('_', $ownerIds) . 'date' . $dateKey . 'start' . $startDateKey . 'end' . $endDateKey
        );
        if (!isset($this->ownersValues[$key])) {
            $this->ownersValues[$key] = $this->doctrine
                ->getRepository('OroCRMSalesBundle:Opportunity')
                ->getForecastOfOpporunitiesData($ownerIds, $date, $this->aclHelper, $start, $end);
        }

        return $this->ownersValues[$key];
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
     * @param WidgetOptionBag $widgetOptions
     *
     * @return array
     */
    protected function getOwnerIds(WidgetOptionBag $widgetOptions)
    {
        $key = spl_object_hash($widgetOptions);
        if (!isset($this->ownerIds[$key])) {
            $owners = $widgetOptions->get('owners');
            $owners = is_array($owners) ? $owners : [$owners];

            $ownerIds = [];
            foreach ($owners as $owner) {
                if (is_object($owner)) {
                    $ownerIds[] = $owner->getId();
                }
            }

            $businessUnitIds = $this->getBusinessUnitsIds($widgetOptions);

            $this->ownerIds[$key] = array_unique(array_merge($this->getUserOwnerIds($businessUnitIds), $ownerIds));
        }

        return $this->ownerIds[$key];
    }

    /**
     * @param int[] $businessUnitIds
     *
     * @return int[]
     */
    protected function getUserOwnerIds(array $businessUnitIds)
    {
        if (!$businessUnitIds) {
            return [];
        }

        $qb = $this->doctrine->getRepository('OroUserBundle:User')
            ->createQueryBuilder('u');

        $qb
            ->select('DISTINCT(u.id)')
            ->join('u.businessUnits', 'bu');
        QueryUtils::applyOptimizedIn($qb, 'bu.id', $businessUnitIds);

        return array_map('current', $qb->getQuery()->getResult());
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     *
     * @return array
     */
    protected function getBusinessUnitsIds(WidgetOptionBag $widgetOptions)
    {
        $businessUnits = $widgetOptions->get('businessUnits');

        $businessUnits = is_array($businessUnits) ? $businessUnits : [$businessUnits];

        $businessUnitIds = [];

        foreach ($businessUnits as $businessUnit) {
            if (is_object($businessUnit)) {
                $businessUnitIds[] = $businessUnit->getId();
            }
        }

        return $businessUnitIds;
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
            'start' => $start ? $start->format('Y-m-d') : null,
            'end'   => $end ? $end->format('Y-m-d') : null
        ];
        if ($usePrevious
            && !empty($dateRange['prev_start'])
            && !empty($dateRange['prev_end'])
        ) {
            /** @var \DateTime $prevStart */
            /** @var \DateTime $prevEnd */
            $prevStart = $dateRange['prev_start'];
            $prevEnd   = $dateRange['prev_end'];
            // current moment
            $now        = $this->dateHelper->getCurrentDateTime();
            $diff       = $start->diff($now);
            $prevMoment = clone $prevStart;
            $prevMoment->add($diff);

            $data = array_merge(
                $data,
                [
                    'current_moment' => $now,
                    'prev_start'     => $prevStart->format('Y-m-d'),
                    'prev_end'       => $prevEnd->format('Y-m-d'),
                    'prev_moment'    => $prevMoment
                ]
            );
        }

        return $data;
    }
}
