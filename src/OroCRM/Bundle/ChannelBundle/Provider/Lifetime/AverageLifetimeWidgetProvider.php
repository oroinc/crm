<?php

namespace OroCRM\Bundle\ChannelBundle\Provider\Lifetime;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\DashboardBundle\Provider\Converters\FilterDateRangeConverter;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class AverageLifetimeWidgetProvider
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var LocaleSettings */
    protected $localeSettings;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var DateFilterProcessor */
    protected $dateFilterProcessor;

    /**
     * @param ManagerRegistry     $registry
     * @param LocaleSettings      $localeSettings
     * @param AclHelper           $aclHelper
     * @param DateFilterProcessor $filterProcessor
     */
    public function __construct(
        ManagerRegistry $registry,
        LocaleSettings $localeSettings,
        AclHelper $aclHelper,
        DateFilterProcessor $filterProcessor
    ) {
        $this->registry            = $registry;
        $this->localeSettings      = $localeSettings;
        $this->aclHelper           = $aclHelper;
        $this->dateFilterProcessor = $filterProcessor;
    }

    /**
     * @param $dateRange array with key start, end and type values is DateTime
     *
     * @return array
     */
    public function getChartData($dateRange)
    {
        $dateData = $this->dateFilterProcessor->getModifiedDateData($dateRange);
        $start    = $this->prepareDate($dateData['value']['start']);
        $end      = $this->prepareDate($dateData['value']['end'], false);
        $dates    = $items = [];
        $period   = new \DatePeriod($start, new \DateInterval('P1M'), $end);
        /** @var \DateTime $dt */
        foreach ($period as $dt) {
            $key         = $dt->format('Y-m');
            $dates[$key] = [
                'month_year' => sprintf('%s-01', $key),
                'amount'     => 0
            ];
        }

        $endDateKey = $end->format('Y-m');
        if (!in_array($endDateKey, array_keys($dates))) {
            $dates[$endDateKey] = [
                'month_year' => sprintf('%s-01', $endDateKey),
                'amount'     => 0
            ];
        }

        $channelNames = $this->registry->getRepository('OroCRMChannelBundle:Channel')
            ->getAvailableChannelNames($this->aclHelper);
        $data         = $this->registry->getRepository('OroCRMChannelBundle:LifetimeValueAverageAggregation')
            ->findForPeriod($start, $end, array_keys($channelNames));

        foreach ($data as $row) {
            $key         = date('Y-m', strtotime(sprintf('%s-%s', $row['year'], $row['month'])));
            $channelName = $channelNames[$row['channelId']]['name'];

            if (!isset($items[$channelName])) {
                $items[$channelName] = $dates;
            }
            $items[$channelName][$key]['amount'] = (int)$row['amount'];
        }

        // restore default keys
        foreach ($items as $channelName => $item) {
            $items[$channelName] = array_values($item);
        }

        return $items;
    }

    /**
     * @param mixed $date
     * @param bool  $isStart
     *
     * @return \DateTime
     */
    protected function prepareDate($date, $isStart = true)
    {
        if (!$date) {
            $date = $isStart
                ? new \DateTime(FilterDateRangeConverter::MIN_DATE, new \DateTimeZone('UTC'))
                : new \DateTime('now', new \DateTimeZone('UTC'));
        }

        return $date instanceof \DateTime
            ? $date
            : new \DateTime($date);
    }
}
