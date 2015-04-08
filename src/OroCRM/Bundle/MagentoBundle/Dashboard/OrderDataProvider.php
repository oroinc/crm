<?php

namespace OroCRM\Bundle\MagentoBundle\Dashboard;

use DateTime;

use Oro\Bundle\ChartBundle\Model\ConfigProvider;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\ChartBundle\Model\ChartView;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\OrderRepository;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;

class OrderDataProvider
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var AclHelper
     */
    protected $aclHelper;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var DateTimeFormatter
     */
    protected $dateTimeFormatter;

    /**
     * @var DateHelper
     */
    protected $dateHelper;

    /**
     * @param ManagerRegistry $registry
     * @param AclHelper $aclHelper
     * @param ConfigProvider $configProvider
     * @param DateTimeFormatter $dateTimeFormatter
     * @param DateHelper $dateHelper
     */
    public function __construct(
        ManagerRegistry $registry,
        AclHelper $aclHelper,
        ConfigProvider $configProvider,
        DateTimeFormatter $dateTimeFormatter,
        DateHelper $dateHelper
    ) {
        $this->registry = $registry;
        $this->aclHelper = $aclHelper;
        $this->configProvider = $configProvider;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->dateHelper = $dateHelper;
    }

    /**
     * @param ChartViewBuilder $viewBuilder
     * @param array            $dateRange
     * @return ChartView
     */
    public function getAverageOrderAmountChartView(ChartViewBuilder $viewBuilder, $dateRange, DateHelper $dateHelper)
    {
        $end               = $dateRange['end'];
        $start             = $dateRange['start'];
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->registry->getRepository('OroCRMMagentoBundle:Order');
        $result = $orderRepository->getAverageOrderAmount($this->aclHelper, $start, $end, $dateHelper);

        $chartOptions = array_merge_recursive(
            ['name' => 'multiline_chart'],
            $this->configProvider->getChartConfig('average_order_amount')
        );
        $chartType = $dateHelper->getFormatStrings($start, $end)['viewType'];
        $chartOptions['data_schema']['label']['type']  = $chartType;
        $chartOptions['data_schema']['label']['label'] =
            sprintf(
                'oro.dashboard.chart.%s.label',
                $chartType
            );


        return $viewBuilder->setOptions($chartOptions)
            ->setArrayData($result)
            ->getView();
    }

    /**
     * @param ChartViewBuilder $viewBuilder
     * @param array $dateRange
     *
     * @return ChartView
     */
    public function getRevenueOverTimeChartView(ChartViewBuilder $viewBuilder, array $dateRange)
    {
        $from = $dateRange['start'];
        $to = $dateRange['end'];

        $items = $this->createRevenueOverTimeCurrentData($from, $to);

        $diff = $to->getTimestamp() - $from->getTimestamp();
        $previousFrom = clone $from;
        $previousFrom->setTimestamp($previousFrom->getTimestamp() - $diff);

        $previousItems = $this->createRevenueOverTimePreviousData($previousFrom, $from, $diff);

        $chartOptions = array_merge_recursive(
            ['name' => 'multiline_chart'],
            $this->configProvider->getChartConfig('revenue_over_time_chart')
        );

        $currentPeriod = sprintf(
            '%s - %s',
            $this->dateTimeFormatter->formatDate($from),
            $this->dateTimeFormatter->formatDate($to)
        );
        $previousPeriod = sprintf(
            '%s - %s',
            $this->dateTimeFormatter->formatDate($previousFrom),
            $this->dateTimeFormatter->formatDate($from)
        );

        return $viewBuilder->setOptions($chartOptions)
            ->setArrayData([
                $previousPeriod => $previousItems,
                $currentPeriod  => $items,
            ])
            ->getView();
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     *
     * @return array
     */
    protected function createRevenueOverTimeCurrentData(DateTime $from, DateTime $to)
    {
        $result = $this->getOrderRepository()->getRevenueOverTime($this->dateHelper, $from, $to);

        $items = $this->dateHelper->getDatePeriod($from, $to);
        foreach ($result as $row) {
            $key = $this->dateHelper->getKey($from, $to, $row);
            $items[$key]['amount'] = $row['amount'];
        }

        return array_combine(range(0, count($items) - 1), array_values($items));
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @param int $diff Timestamp
     *
     * @return array
     */
    protected function createRevenueOverTimePreviousData(DateTime $from, DateTime $to, $diff)
    {
        $result = $this->getOrderRepository()->getRevenueOverTime($this->dateHelper, $from, $to);

        $items = $this->dateHelper->getDatePeriod($from, $to);
        foreach ($result as $row) {
            $key = $this->dateHelper->getKey($from, $to, $row);
            $items[$key]['amount'] = $row['amount'];
        }

        $currentFrom = $to;
        $currentTo = clone $to;
        $currentTo->setTimestamp($currentFrom->getTimestamp() + $diff);

        $currentItems = $this->dateHelper->getDatePeriod($currentFrom, $currentTo);

        $mixedItems = array_combine(array_keys($currentItems), array_values($items));
        foreach ($mixedItems as $k => $v) {
            $v['date'] = $k;
            $currentItems[$k] = $v;
        }

        return array_combine(range(0, count($currentItems) - 1), array_values($currentItems));
    }

    /**
     * @return OrderRepository
     */
    protected function getOrderRepository()
    {
        return $this->registry->getRepository('OroCRMMagentoBundle:Order');
    }
}
