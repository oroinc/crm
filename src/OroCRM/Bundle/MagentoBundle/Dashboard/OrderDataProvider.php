<?php

namespace OroCRM\Bundle\MagentoBundle\Dashboard;

use DateTime;

use Oro\Bundle\ChartBundle\Model\ConfigProvider;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\ChartBundle\Model\ChartView;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;

use OroCRM\Bundle\MagentoBundle\Entity\Repository\OrderRepository;

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
     * @param ManagerRegistry   $registry
     * @param AclHelper         $aclHelper
     * @param ConfigProvider    $configProvider
     * @param DateTimeFormatter $dateTimeFormatter
     * @param DateHelper        $dateHelper
     */
    public function __construct(
        ManagerRegistry $registry,
        AclHelper $aclHelper,
        ConfigProvider $configProvider,
        DateTimeFormatter $dateTimeFormatter,
        DateHelper $dateHelper
    ) {
        $this->registry          = $registry;
        $this->aclHelper         = $aclHelper;
        $this->configProvider    = $configProvider;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->dateHelper        = $dateHelper;
    }

    /**
     * @param ChartViewBuilder $viewBuilder
     * @param array            $dateRange
     * @param DateHelper       $dateHelper
     *
     * @return ChartView
     */
    public function getAverageOrderAmountChartView(ChartViewBuilder $viewBuilder, $dateRange, DateHelper $dateHelper)
    {
        list($start, $end) = $dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Customer', 'createdAt');
        if ($start === null && $end === null) {
            $start = new \DateTime(DateHelper::MIN_DATE, new \DateTimeZone('UTC'));
            $end   = new \DateTime('now', new \DateTimeZone('UTC'));
        }
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->registry->getRepository('OroCRMMagentoBundle:Order');
        $result          = $orderRepository->getAverageOrderAmount($this->aclHelper, $start, $end, $dateHelper);

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
     * @param array            $dateRange
     *
     * @return ChartView
     */
    public function getOrdersOverTimeChartView(ChartViewBuilder $viewBuilder, array $dateRange)
    {
        /* @var $from DateTime */
        /* @var $to DateTime */
        list($from, $to) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Order', 'createdAt');
        if ($from === null && $to === null) {
            $from = new \DateTime(DateHelper::MIN_DATE, new \DateTimeZone('UTC'));
            $to   = new \DateTime('now', new \DateTimeZone('UTC'));
        }
        $result = $this->getOrderRepository()->getOrdersOverTime($this->aclHelper, $this->dateHelper, $from, $to);
        $items  = $this->dateHelper->convertToCurrentPeriod($from, $to, $result, 'cnt', 'count');

        $previousFrom   = $this->createPreviousFrom($from, $to);
        $previousResult = $this->getOrderRepository()->getOrdersOverTime(
            $this->aclHelper,
            $this->dateHelper,
            $previousFrom,
            $from
        );
        $previousItems  = $this->dateHelper->combinePreviousDataWithCurrentPeriod(
            $previousFrom,
            $from,
            $previousResult,
            'cnt',
            'count'
        );

        $chartType = $this->dateHelper->getFormatStrings($from, $to)['viewType'];
        $data      = [
            $this->createPeriodLabel($previousFrom, $from) => $previousItems,
            $this->createPeriodLabel($from, $to)           => $items,
        ];

        return $this->createPeriodChartView($viewBuilder, 'orders_over_time_chart', $chartType, $data);
    }

    /**
     * @param ChartViewBuilder $viewBuilder
     * @param array            $dateRange
     *
     * @return ChartView
     */
    public function getRevenueOverTimeChartView(ChartViewBuilder $viewBuilder, array $dateRange)
    {
        /* @var $from DateTime */
        /* @var $to DateTime */
        list($from, $to) = $this->dateHelper->getPeriod($dateRange, 'OroCRMMagentoBundle:Order', 'createdAt');
        if ($from === null && $to === null) {
            $from = new \DateTime(DateHelper::MIN_DATE, new \DateTimeZone('UTC'));
            $to   = new \DateTime('now', new \DateTimeZone('UTC'));
        }
        $orderRepository = $this->getOrderRepository();

        $result = $orderRepository->getRevenueOverTime($this->aclHelper, $this->dateHelper, $from, $to);
        $items  = $this->dateHelper->convertToCurrentPeriod($from, $to, $result, 'amount', 'amount');

        $previousFrom   = $this->createPreviousFrom($from, $to);
        $previousResult = $orderRepository->getRevenueOverTime(
            $this->aclHelper,
            $this->dateHelper,
            $previousFrom,
            $from
        );
        $previousItems  = $this->dateHelper->combinePreviousDataWithCurrentPeriod(
            $previousFrom,
            $from,
            $previousResult,
            'amount',
            'amount'
        );

        $chartType = $this->dateHelper->getFormatStrings($from, $to)['viewType'];
        $data      = [
            $this->createPeriodLabel($previousFrom, $from) => $previousItems,
            $this->createPeriodLabel($from, $to)           => $items,
        ];

        return $this->createPeriodChartView($viewBuilder, 'revenue_over_time_chart', $chartType, $data);
    }

    /**
     * @param ChartViewBuilder $viewBuilder
     * @param string           $chart
     * @param string           $type
     * @param array            $data
     *
     * @return ChartView
     */
    protected function createPeriodChartView(ChartViewBuilder $viewBuilder, $chart, $type, array $data)
    {
        $chartOptions = array_merge_recursive(
            ['name' => 'multiline_chart'],
            $this->configProvider->getChartConfig($chart)
        );
        $chartOptions['data_schema']['label']['type']  = $type;
        $chartOptions['data_schema']['label']['label'] =
            sprintf(
                'oro.dashboard.chart.%s.label',
                $type
            );

        return $viewBuilder->setOptions($chartOptions)
            ->setArrayData($data)
            ->getView();
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     *
     * @return DateTime
     */
    protected function createPreviousFrom(DateTime $from, DateTime $to)
    {
        $diff         = $to->getTimestamp() - $from->getTimestamp();
        $previousFrom = clone $from;
        $previousFrom->setTimestamp($previousFrom->getTimestamp() - $diff);

        return $previousFrom;
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
     * @return OrderRepository
     */
    protected function getOrderRepository()
    {
        return $this->registry->getRepository('OroCRMMagentoBundle:Order');
    }
}
