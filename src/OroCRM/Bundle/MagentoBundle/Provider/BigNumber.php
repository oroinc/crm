<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use LogicException;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;

use OroCRM\Bundle\MagentoBundle\Provider\Formatter\BigNumberFormatter;
use OroCRM\Bundle\MagentoBundle\Provider\Helper\BigNumberDateHelper;

class BigNumber
{
    /** @var BigNumberFormatter */
    protected $bigNumberFormatter;

    /** @var DateTimeFormatter */
    protected $dateTimeFormatter;

    /** @var BigNumberDateHelper */
    protected $dateHelper;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var object[] */
    protected $valueProviders = [];

    /**
     * @param BigNumberFormatter  $bigNumberFormatter
     * @param DateTimeFormatter   $dateTimeFormatter
     * @param BigNumberDateHelper $dateHelper
     * @param TranslatorInterface $translator
     */
    public function __construct(
        BigNumberFormatter $bigNumberFormatter,
        DateTimeFormatter $dateTimeFormatter,
        BigNumberDateHelper $dateHelper,
        TranslatorInterface $translator
    ) {
        $this->bigNumberFormatter = $bigNumberFormatter;
        $this->dateTimeFormatter  = $dateTimeFormatter;
        $this->dateHelper         = $dateHelper;
        $this->translator         = $translator;
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @param                 $getterName
     * @param                 $dataType
     * @param bool            $lessIsBetter
     * @param bool            $lastWeek
     * @return array
     */
    public function getBigNumberValues(
        WidgetOptionBag $widgetOptions,
        $getterName, $dataType,
        $lessIsBetter = false, $lastWeek = false
    ) {
        $getter           = $this->getGetter($getterName);
        $lessIsBetter     = (bool)$lessIsBetter;
        $result           = [];
        $dateRange        = $lastWeek ? $this->dateHelper->getLastWeekPeriod() : $widgetOptions->get('dateRange');
        $value            = call_user_func($getter, $dateRange);
        $result['value']  = $this->bigNumberFormatter->formatValue($value, $dataType);
        $previousInterval = $widgetOptions->get('usePreviousInterval', []);

        if (count($previousInterval)) {
            if ($lastWeek) {
                $previousInterval = $this->dateHelper->getLastWeekPeriod(-1);
            }

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
}
