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

        $previousItems = $this->createRevenueOverTimePreviousData($previousFrom, $from);

        $chartOptions = array_merge_recursive(
            ['name' => 'multiline_chart'],
            $this->configProvider->getChartConfig('revenue_over_time_chart')
        );
        $chartType = $this->dateHelper->getFormatStrings($from, $to)['viewType'];
        $chartOptions['data_schema']['label']['type']  = $chartType;
        $chartOptions['data_schema']['label']['label'] =
            sprintf(
                'oro.dashboard.chart.%s.label',
                $chartType
            );

        $currentPeriod = $this->createPeriodLabel($from, $to);
        $previousPeriod = $this->createPeriodLabel($previousFrom, $from);

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
     * @return string
     */
    protected function createPeriodLabel(DateTime $from, DateTime $to)
    {
        return sprintf(
            '%s - %s',
            $this->dateTimeFormatter->formatDate($from),
            $this->dateTimeFormatter->formatDate($to)
        );
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     *
     * @return array
     */
    protected function createRevenueOverTimeCurrentData(DateTime $from, DateTime $to)
    {
        $result = $this->getOrderRepository()->getRevenueOverTime($this->aclHelper, $this->dateHelper, $from, $to);

        return $this->dateHelper->convertToCurrentPeriod($from, $to, $result, 'amount', 'amount');
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @param int $diff Timestamp
     *
     * @return array
     */
    protected function createRevenueOverTimePreviousData(DateTime $from, DateTime $to)
    {
        $result = $this->getOrderRepository()->getRevenueOverTime($this->aclHelper, $this->dateHelper, $from, $to);

        return $this->dateHelper->convertToPreviousPeriod($from, $to, $result, 'amount', 'amount');
    }

    /**
     * @return OrderRepository
     */
    protected function getOrderRepository()
    {
        return $this->registry->getRepository('OroCRMMagentoBundle:Order');
    }
}
