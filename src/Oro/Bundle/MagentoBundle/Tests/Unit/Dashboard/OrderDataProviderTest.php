<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Converter;

use DateTime;
use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatterInterface;
use Oro\Bundle\MagentoBundle\Dashboard\OrderDataProvider;

class OrderDataProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $registry;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $aclHelper;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $configProvider;

    /** @var OrderDataProvider */
    protected $dataProvider;

    /** @var DateHelper */
    protected $dateHelper;

    /** @var DateTimeFormatterInterface */
    protected $dateTimeFormatter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->registry = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->aclHelper = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configProvider = $this->getMockBuilder('Oro\Bundle\ChartBundle\Model\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dateHelper = $this->getMockBuilder('Oro\Bundle\DashboardBundle\Helper\DateHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeFormatter = $this
            ->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataProvider = new OrderDataProvider(
            $this->registry,
            $this->aclHelper,
            $this->configProvider,
            $this->dateTimeFormatter,
            $this->dateHelper
        );
    }

    public function testGetAverageOrderAmountByCustomerChartView()
    {
        $sourceOrderData   = [
            'First'  => [['month' => '2014-09-01', 'amount' => 3]],
            'Second' => [['month' => '2014-09-01', 'amount' => 5]]
        ];
        $expectedArrayData = [
            'First'  => [['month' => '2014-09-01', 'amount' => 3]],
            'Second' => [['month' => '2014-09-01', 'amount' => 5]]
        ];
        $expectedOptions   = [
            'name'        => 'multiline_chart',
            'data_schema' => [
                'label' => [
                    'field_name' => 'month',
                    'label'      => 'oro.dashboard.chart.month.label',
                    'type'       => 'month'
                ],
                'value' => [
                    'field_name' => 'amount',
                    'label'      => 'oro.magento.dashboard.average_order_amount_chart.order_amount',
                    'type'       => 'currency'
                ]
            ]
        ];
        $chartConfig       = [
            'data_schema' => [
                'label' => [
                    'field_name' => 'month',
                    'label'      => 'oro.magento.dashboard.average_order_amount_chart.month',
                    'type'       => 'month'
                ],
                'value' => [
                    'field_name' => 'amount',
                    'label'      => 'oro.magento.dashboard.average_order_amount_chart.order_amount',
                    'type'       => 'currency'
                ]
            ]
        ];

        $orderRepository = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\Repository\OrderRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $start           = new \DateTime('2012-01-01');
        $end             = new \DateTime('2015-01-01');
        $dateHelper      = $this->getMockBuilder('Oro\Bundle\DashboardBundle\Helper\DateHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $dateHelper->expects($this->any())
            ->method('getFormatStrings')
            ->willReturn(['viewType' => 'month']);
        $dateHelper->expects($this->once())
            ->method('getPeriod')
            ->willReturnCallback(function ($dateRange) {
                return [$dateRange['start'], $dateRange['end']];
            });
        $orderRepository->expects($this->once())
            ->method('getAverageOrderAmount')
            ->with($this->aclHelper, $start, $end, $dateHelper)
            ->will($this->returnValue($sourceOrderData));

        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroMagentoBundle:Order')
            ->will($this->returnValue($orderRepository));

        $chartView = $this->getMockBuilder('Oro\Bundle\ChartBundle\Model\ChartView')
            ->disableOriginalConstructor()
            ->getMock();

        $chartViewBuilder = $this->getMockBuilder('Oro\Bundle\ChartBundle\Model\ChartViewBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $chartViewBuilder->expects($this->once())
            ->method('setOptions')
            ->with($expectedOptions)
            ->will($this->returnSelf());
        $chartViewBuilder->expects($this->once())
            ->method('setArrayData')
            ->with($expectedArrayData)
            ->will($this->returnSelf());
        $chartViewBuilder->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($chartView));

        $this->configProvider->expects($this->once())
            ->method('getChartConfig')
            ->with('average_order_amount')
            ->will($this->returnValue($chartConfig));

        $this->assertEquals(
            $chartView,
            $this->dataProvider->getAverageOrderAmountChartView(
                $chartViewBuilder,
                ['start' => $start, 'end' => $end],
                $dateHelper
            )
        );
    }

    /**
     * @dataProvider getOrdersOverTimeChartViewDataProvider
     */
    public function testGetOrdersOverTimeChartView($sourceData, $expectedArrayData, $expectedOptions, $chartConfig)
    {
        $from = new DateTime('2015-05-10');
        $to = new DateTime('2015-05-15');
        $previousFrom = new DateTime('2015-05-05');
        $previousTo = new DateTime('2015-05-10');

        $this->dateHelper->expects($this->any())
            ->method('getFormatStrings')
            ->willReturn(
                [
                    'viewType' => 'day'
                ]
            );
        $this->dateHelper->expects($this->once())
            ->method('getPeriod')
            ->willReturnCallback(function ($dateRange) {
                return [$dateRange['start'], $dateRange['end']];
            });
        $this->dateHelper->expects($this->once())
            ->method('convertToCurrentPeriod')
            ->will($this->returnValue($expectedArrayData['2015-05-10 - 2015-05-15']));
        $this->dateHelper->expects($this->once())
            ->method('combinePreviousDataWithCurrentPeriod')
            ->will($this->returnValue($expectedArrayData['2015-05-05 - 2015-05-10']));

        $this->dateTimeFormatter->expects($this->exactly(4))
            ->method('formatDate')
            ->will($this->onConsecutiveCalls('2015-05-05', '2015-05-10', '2015-05-10', '2015-05-15'));

        $orderRepository = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\Repository\OrderRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orderRepository->expects($this->at(0))
            ->method('getOrdersOverTime')
            ->with($this->aclHelper, $this->dateHelper, $from, $to)
            ->will($this->returnValue($sourceData[0]));
        $orderRepository->expects($this->at(1))
            ->method('getOrdersOverTime')
            ->with($this->aclHelper, $this->dateHelper, $previousFrom, $previousTo)
            ->will($this->returnValue($sourceData[1]));

        $this->registry->expects($this->any())
            ->method('getRepository')
            ->with('OroMagentoBundle:Order')
            ->will($this->returnValue($orderRepository));

        $chartView = $this->getMockBuilder('Oro\Bundle\ChartBundle\Model\ChartView')
            ->disableOriginalConstructor()
            ->getMock();

        $chartViewBuilder = $this->getMockBuilder('Oro\Bundle\ChartBundle\Model\ChartViewBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $chartViewBuilder->expects($this->once())
            ->method('setOptions')
            ->with($expectedOptions)
            ->will($this->returnSelf());
        $chartViewBuilder->expects($this->once())
            ->method('setArrayData')
            ->with($expectedArrayData)
            ->will($this->returnSelf());
        $chartViewBuilder->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($chartView));

        $this->configProvider->expects($this->once())
            ->method('getChartConfig')
            ->with('orders_over_time_chart')
            ->will($this->returnValue($chartConfig));

        $this->assertEquals(
            $chartView,
            $this->dataProvider->getOrdersOverTimeChartView($chartViewBuilder, ['start' => $from, 'end' => $to])
        );
    }

    public function getOrdersOverTimeChartViewDataProvider()
    {
        return [
            [
                'sourceOrderData' => [
                    [
                        [
                            'yearCreated'  => '2015',
                            'monthCreated' => '05',
                            'dayCreated'   => '12',
                            'cnt'          => 3,
                        ],
                    ],
                    [
                        [
                            'yearCreated'  => '2015',
                            'monthCreated' => '05',
                            'dayCreated'   => '07',
                            'cnt'          => 5,
                        ],
                    ],
                ],
                'expectedArrayData' => [
                    '2015-05-05 - 2015-05-10' => [
                        ['date' => '2015-05-10'],
                        ['date' => '2015-05-11'],
                        ['date' => '2015-05-12', 'count' => 5],
                        ['date' => '2015-05-13'],
                        ['date' => '2015-05-14'],
                    ],
                    '2015-05-10 - 2015-05-15' => [
                        ['date' => '2015-05-10'],
                        ['date' => '2015-05-11'],
                        ['date' => '2015-05-12', 'count' => 3],
                        ['date' => '2015-05-13'],
                        ['date' => '2015-05-14'],
                    ],
                ],
                'expectedOptions' => [
                    'name' => 'multiline_chart',
                    'data_schema' => [
                        'label' => [
                            'field_name' => 'date',
                            'label' => 'oro.dashboard.chart.day.label',
                            'type' => 'day',
                        ],
                        'value' => [
                            'field_name' => 'count',
                            'label' => 'oro.magento.dashboard.orders_over_time_chart.order_count',
                            'type' => 'integer'
                        ],
                    ],
                ],
                'chartConfig' => [
                    'data_schema' => [
                        'label' => [
                            'field_name' => 'date',
                            'label' => 'oro.dashboard.chart.day.label',
                            'type' => 'day',
                        ],
                        'value' => [
                            'field_name' => 'count',
                            'label' => 'oro.magento.dashboard.orders_over_time_chart.order_count',
                            'type' => 'integer'
                        ],
                    ],
                ]
            ]
        ];
    }

    /**
     * @dataProvider getRevenueOverTimeChartViewDataProvider
     */
    public function testGetRevenueOverTimeChartView($sourceData, $expectedArrayData, $expectedOptions, $chartConfig)
    {
        $from = new DateTime('2015-05-10');
        $to = new DateTime('2015-05-15');
        $previousFrom = new DateTime('2015-05-05');
        $previousTo = new DateTime('2015-05-10');

        $this->dateHelper->expects($this->any())
            ->method('getFormatStrings')
            ->willReturn(
                [
                    'viewType' => 'day'
                ]
            );
        $this->dateHelper->expects($this->once())
            ->method('getPeriod')
            ->willReturnCallback(function ($dateRange) {
                return [$dateRange['start'], $dateRange['end']];
            });
        $this->dateHelper->expects($this->once())
            ->method('convertToCurrentPeriod')
            ->will($this->returnValue($expectedArrayData['2015-05-10 - 2015-05-15']));
        $this->dateHelper->expects($this->once())
            ->method('combinePreviousDataWithCurrentPeriod')
            ->will($this->returnValue($expectedArrayData['2015-05-05 - 2015-05-10']));
        $this->dateTimeFormatter->expects($this->exactly(4))
            ->method('formatDate')
            ->will($this->onConsecutiveCalls('2015-05-05', '2015-05-10', '2015-05-10', '2015-05-15'));

        $orderRepository = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\Repository\OrderRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orderRepository->expects($this->at(0))
            ->method('getRevenueOverTime')
            ->with($this->aclHelper, $this->dateHelper, $from, $to)
            ->will($this->returnValue($sourceData[0]));
        $orderRepository->expects($this->at(1))
            ->method('getRevenueOverTime')
            ->with($this->aclHelper, $this->dateHelper, $previousFrom, $previousTo)
            ->will($this->returnValue($sourceData[1]));
        $this->registry->expects($this->any())
            ->method('getRepository')
            ->with('OroMagentoBundle:Order')
            ->will($this->returnValue($orderRepository));
        $chartView = $this->getMockBuilder('Oro\Bundle\ChartBundle\Model\ChartView')
            ->disableOriginalConstructor()
            ->getMock();
        $chartViewBuilder = $this->getMockBuilder('Oro\Bundle\ChartBundle\Model\ChartViewBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $chartViewBuilder->expects($this->once())
            ->method('setOptions')
            ->with($expectedOptions)
            ->will($this->returnSelf());
        $chartViewBuilder->expects($this->once())
            ->method('setArrayData')
            ->with($expectedArrayData)
            ->will($this->returnSelf());
        $chartViewBuilder->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($chartView));
        $this->configProvider->expects($this->once())
            ->method('getChartConfig')
            ->with('revenue_over_time_chart')
            ->will($this->returnValue($chartConfig));
        $this->assertEquals(
            $chartView,
            $this->dataProvider->getRevenueOverTimeChartView($chartViewBuilder, ['start' => $from, 'end' => $to])
        );
    }

    public function getRevenueOverTimeChartViewDataProvider()
    {
        return [
            [
                'sourceOrderData' => [
                    [
                        [
                            'yearCreated'  => '2015',
                            'monthCreated' => '05',
                            'dayCreated'   => '12',
                            'amount'       => 200,
                        ],
                    ],
                    [
                        [
                            'yearCreated'  => '2015',
                            'monthCreated' => '05',
                            'dayCreated'   => '07',
                            'amount'       => 100,
                        ],
                    ],
                ],
                'expectedArrayData' => [
                    '2015-05-05 - 2015-05-10' => [
                        ['date' => '2015-05-10'],
                        ['date' => '2015-05-11'],
                        ['date' => '2015-05-12', 'amount' => 100],
                        ['date' => '2015-05-13'],
                        ['date' => '2015-05-14'],
                    ],
                    '2015-05-10 - 2015-05-15' => [
                        ['date' => '2015-05-10'],
                        ['date' => '2015-05-11'],
                        ['date' => '2015-05-12', 'amount' => 200],
                        ['date' => '2015-05-13'],
                        ['date' => '2015-05-14'],
                    ],
                ],
                'expectedOptions' => [
                    'name' => 'multiline_chart',
                    'data_schema' => [
                        'label' => [
                            'field_name' => 'date',
                            'label' => 'oro.dashboard.chart.day.label',
                            'type' => 'day',
                        ],
                        'value' => [
                            'field_name' => 'amount',
                            'label' => 'oro.magento.dashboard.revenue_over_time_chart.revenue',
                            'type' => 'currency'
                        ],
                    ],
                ],
                'chartConfig' => [
                    'data_schema' => [
                        'label' => [
                            'field_name' => 'date',
                            'label' => 'oro.dashboard.chart.day.label',
                            'type' => 'day',
                        ],
                        'value' => [
                            'field_name' => 'amount',
                            'label' => 'oro.magento.dashboard.revenue_over_time_chart.revenue',
                            'type' => 'currency'
                        ],
                    ],
                ],
            ]
        ];
    }
}
