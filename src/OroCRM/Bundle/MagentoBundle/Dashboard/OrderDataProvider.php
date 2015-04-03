<?php

namespace OroCRM\Bundle\MagentoBundle\Dashboard;

use DateTime;
use DateInterval;

use Oro\Bundle\ChartBundle\Model\ConfigProvider;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\ChartBundle\Model\ChartView;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
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
     * @param ManagerRegistry $registry
     * @param AclHelper $aclHelper
     * @param ConfigProvider $configProvider
     * @param DateTimeFormatter $dateTimeFormatter
     */
    public function __construct(
        ManagerRegistry $registry,
        AclHelper $aclHelper,
        ConfigProvider $configProvider,
        DateTimeFormatter $dateTimeFormatter
    ) {
        $this->registry = $registry;
        $this->aclHelper = $aclHelper;
        $this->configProvider = $configProvider;
        $this->dateTimeFormatter = $dateTimeFormatter;
    }

    /**
     * @param ChartViewBuilder $viewBuilder
     * @return ChartView
     */
    public function getAverageOrderAmountChartView(ChartViewBuilder $viewBuilder)
    {
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->registry->getRepository('OroCRMMagentoBundle:Order');
        $result = $orderRepository->getAverageOrderAmount($this->aclHelper);

        // prepare chart items
        $items = [];
        foreach ($result as $channel) {
            $channelName = $channel['name'];
            $channelData = $channel['data'];

            $items[$channelName] = [];

            foreach ($channelData as $year => $monthData) {
                foreach ($monthData as $month => $amount) {
                    $items[$channelName][] = [
                        'month' => sprintf('%04d-%02d-01', $year, $month),
                        'amount' => $amount
                    ];
                }
            }
        }

        $chartOptions = array_merge_recursive(
            ['name' => 'multiline_chart'],
            $this->configProvider->getChartConfig('average_order_amount')
        );

        return $viewBuilder->setOptions($chartOptions)
            ->setArrayData($items)
            ->getView();
    }

    /**
     * @param ChartViewBuilder $viewBuilder
     * @param DateTime $from
     * @param DateTime $to
     *
     * @return ChartView
     */
    public function getRevenueOverTimeChartView(ChartViewBuilder $viewBuilder, DateTime $from, DateTime $to)
    {
        $items = $this->createRevenueOverTimeCurrentData($from, $to);

        $interval = $from->diff($to);
        $fromDate = clone $from;
        $previousFrom = $fromDate->sub($interval);
        $previousItems = $this->createRevenueOverTimePreviousData($previousFrom, $from, $interval);

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
        $result = $this->getOrderRepository()->getRevenueOverTime($from, $to);

        $items = [];
        foreach ($result as $year => $yearData) {
            foreach ($yearData as $month => $monthData) {
                foreach ($monthData as $day => $amount) {
                    $items[] = [
                        'date' => sprintf('%04d-%02d-%02d', $year, $month, $day),
                        'amount' => $amount,
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @param DateInterval $interval
     *
     * @return array
     */
    protected function createRevenueOverTimePreviousData(DateTime $from, DateTime $to, DateInterval $interval)
    {
        $result = $this->getOrderRepository()->getRevenueOverTime($from, $to);

        $items = [];
        foreach ($result as $year => $yearData) {
            foreach ($yearData as $month => $monthData) {
                foreach ($monthData as $day => $amount) {
                    $date = new DateTime(sprintf('%04d-%02d-%02d', $year, $month, $day));
                    $date->add($interval);
                    $items[] = [
                        'date' => $date->format('Y-m-d'),
                        'amount' => $amount,
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * @return OrderRepository
     */
    protected function getOrderRepository()
    {
        return $this->registry->getRepository('OroCRMMagentoBundle:Order');
    }
}
