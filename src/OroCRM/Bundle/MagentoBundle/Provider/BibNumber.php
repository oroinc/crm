<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BibNumber
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var NumberFormatter */
    protected $numberFormatter;

    /** @var DateTimeFormatter */
    protected $dateTimeFormatter;

    public function __construct(RegistryInterface $doctrine, NumberFormatter $numberFormatter, DateTimeFormatter $dateTimeFormatter)
    {
        $this->doctrine = $doctrine;
        $this->numberFormatter = $numberFormatter;
        $this->dateTimeFormatter = $dateTimeFormatter;
    }

    public function getRevenueValues(WidgetOptionBag $widgetOptions)
    {
        $repo = $this->doctrine->getRepository('OroCRMMagentoBundle:Order');

        $dateRange = $widgetOptions->get('dateRange');

        $from = $dateRange['start'];
        $to = $dateRange['end'];
        $result['valueType'] = 'currency';
        $value = $repo->getRevenueValueByDatePeriod($from, $to);
        $result['value'] = $this->numberFormatter->formatCurrency($value);

        $previousInterval = $widgetOptions->get('usePreviousInterval', []);
        if (count($previousInterval)) {
            $previousFrom = $previousInterval['start'];
            $previousTo = $previousInterval['end'];

            $pastResult = $repo->getRevenueValueByDatePeriod($previousFrom, $previousTo);
            $deviation = $value - $pastResult;
            $result['deviation'] = $this->numberFormatter->formatCurrency($deviation);
            $result['isPositive'] = $deviation > 0;
            $result['previousRange'] = sprintf(
                '%s - %s',
                $this->dateTimeFormatter->formatDate($previousFrom),
                $this->dateTimeFormatter->formatDate($previousTo)
            );
        }

        return $result;
    }

    public function getOrdersNumberValues(WidgetOptionBag $widgetOptions)
    {
        $repo = $this->doctrine->getRepository('OroCRMMagentoBundle:Order');

        $dateRange = $widgetOptions->get('dateRange');

        $from = $dateRange['start'];
        $to = $dateRange['end'];
        $result['valueType'] = 'currency';
        $value = $repo->getOrdersNumberByDatePeriod($from, $to);
        $result['value'] = $value;

        $previousInterval = $widgetOptions->get('usePreviousInterval', []);
        if (count($previousInterval)) {
            $previousFrom = $previousInterval['start'];
            $previousTo = $previousInterval['end'];

            $pastResult = $repo->getOrdersNumberByDatePeriod($previousFrom, $previousTo);
            $deviation = $value - $pastResult;
            $result['deviation'] = $deviation;
            $result['isPositive'] = $deviation > 0;
            $result['previousRange'] = sprintf(
                '%s - %s',
                $this->dateTimeFormatter->formatDate($previousFrom),
                $this->dateTimeFormatter->formatDate($previousTo)
            );
        }

        return $result;
    }
}