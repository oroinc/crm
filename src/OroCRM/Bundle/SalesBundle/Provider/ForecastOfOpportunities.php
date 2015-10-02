<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

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

    /** @var  array */
    protected $ownersValues;

    /**
     * @param RegistryInterface   $doctrine
     * @param NumberFormatter     $numberFormatter
     * @param DateTimeFormatter   $dateTimeFormatter
     * @param AclHelper           $aclHelper
     * @param TranslatorInterface $translator
     */
    public function __construct(
        RegistryInterface $doctrine,
        NumberFormatter $numberFormatter,
        DateTimeFormatter $dateTimeFormatter,
        AclHelper $aclHelper,
        TranslatorInterface $translator
    ) {
        $this->doctrine          = $doctrine;
        $this->numberFormatter   = $numberFormatter;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->aclHelper         = $aclHelper;
        $this->translator        = $translator;
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @param string          $getterName
     * @param string          $dataType
     * @param bool            $lessIsBetter
     * @return array
     */
    public function getForecastOfOpportunitiesValues(
        WidgetOptionBag $widgetOptions,
        $getterName,
        $dataType,
        $lessIsBetter = false
    ) {
        $lessIsBetter     = (bool)$lessIsBetter;
        $result           = [];
        $ownerIds         = $this->getOwnerIds($widgetOptions);
        $value            = $this->{$getterName}($ownerIds);
        $result['value']  = $this->formatValue($value, $dataType);
        $compareToDate = $widgetOptions->get('compareToDate');

        if (isset($compareToDate['useDate']) && $compareToDate['useDate']) {
            $pastResult = $this->{$getterName}($ownerIds, $compareToDate['date']);

            $result['deviation'] = $this->translator
                ->trans('orocrm.sales.dashboard.forecast_of_opportunities.no_changes');

            $result = $this->prepareData($dataType, $lessIsBetter, $pastResult, $value - $pastResult, $result);

            $result['previousRange'] = $this->dateTimeFormatter->formatDate($compareToDate['date']);
        }

        return $result;
    }

    /**
     * @param array $ownerIds
     * @param null $compareToDate
     * @return int
     */
    protected function getInProgressValues($ownerIds, $compareToDate = null)
    {
        $values = $this->getOwnersValues($ownerIds, $compareToDate);

        return $values && isset($values['inProgressCount']) ? $values['inProgressCount'] : 0;
    }

    /**
     * @param array $ownerIds
     * @param null $compareToDate
     * @return int
     */
    protected function getTotalForecastValues($ownerIds, $compareToDate = null)
    {
        $values = $this->getOwnersValues($ownerIds, $compareToDate);

        return $values && isset($values['budgetAmount']) ? $values['budgetAmount'] : 0;
    }

    /**
     * @param array $ownerIds
     * @param null $compareToDate
     * @return int
     */
    protected function getWeightedForecastValues($ownerIds, $compareToDate = null)
    {
        $values = $this->getOwnersValues($ownerIds, $compareToDate);

        return $values && isset($values['weightedForecast']) ? $values['weightedForecast'] : 0;
    }

    /**
     * @param array $ownerIds
     * @param \DateTime|string|int $date
     * @return mixed
     */
    protected function getOwnersValues(array $ownerIds, $date)
    {
        $key = sha1(implode('_', $ownerIds) . $this->dateTimeFormatter->formatDate($date));
        if (!isset($this->ownersValues[$key])) {
            $this->ownersValues[$key] = $this->doctrine
                ->getRepository('OroCRMSalesBundle:Opportunity')
                ->getForecastOfOpporunitiesData($ownerIds, $date, $this->aclHelper);
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
                $deviationPercent = $deviation / $pastResult;
                $result['deviation'] = sprintf(
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
                $result['deviation'] = $this->formatValue($deviation, $dataType, true);
                if (!$lessIsBetter) {
                    $result['isPositive'] = $deviation > 0;
                } else {
                    $result['isPositive'] = !($deviation > 0);
                }
            }
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
        $owners = $widgetOptions->get('owners');
        $owners = is_array($owners) ? $owners : [$owners];

        $ownerIds = [];
        foreach ($owners as $owner) {
            if (is_object($owner)) {
                $ownerIds[] = $owner->getId();
            }
        }

        $businessUnitIds = $this->getBusinessUnitsIds($widgetOptions);

        if (!empty($businessUnitIds)) {
            //need to load from repository, because it returns unserialized object without users from widget options
            $businessUnits = $this->doctrine
                ->getRepository('OroOrganizationBundle:BusinessUnit')
                ->findById($businessUnitIds);

            foreach ($businessUnits as $businessUnit) {
                $users = $businessUnit->getUsers();
                foreach ($users as $user) {
                    $ownerIds[] = $user->getId();
                }
            }

            $ownerIds = array_unique($ownerIds);
        }

        return $ownerIds;
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
}
