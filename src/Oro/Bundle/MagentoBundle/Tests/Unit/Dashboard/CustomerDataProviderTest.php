<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Converter;

use Oro\Bundle\MagentoBundle\Dashboard\CustomerDataProvider;

class CustomerDataProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $registry;

    /** @var CustomerDataProvider */
    protected $dataProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $aclHelper;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $configProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $dateHelper;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->registry   = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->aclHelper  = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configProvider = $this->getMockBuilder('Oro\Bundle\ChartBundle\Model\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateHelper = $this->getMockBuilder('Oro\Bundle\DashboardBundle\Helper\DateHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataProvider = new CustomerDataProvider(
            $this->registry,
            $this->aclHelper,
            $this->configProvider,
            $this->dateHelper
        );
    }

    /**
     * @param array $channels
     * @param array $sourceData
     * @param array $expectedArrayData
     * @param array $expectedOptions
     * @param array $chartConfig
     * @param array $dateRange
     *
     * @dataProvider getNewCustomerChartViewDataProvider
     */
    public function testGetNewCustomerChartView(
        array $channels,
        array $sourceData,
        array $expectedArrayData,
        array $expectedOptions,
        array $chartConfig,
        array $dateRange
    ) {
        $channelRepository = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Entity\Repository\ChannelRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $customerRepository = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\Repository\CustomerRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry->expects($this->any())
            ->method('getRepository')
            ->will(
                $this->returnCallback(
                    function ($entityName) use ($channelRepository, $customerRepository) {
                        if ($entityName == 'OroChannelBundle:Channel') {
                            return $channelRepository;
                        }
                        return $customerRepository;
                    }
                )
            );

        $channelRepository->expects($this->once())
            ->method('getAvailableChannelNames')
            ->with($this->aclHelper, 'magento')
            ->will($this->returnValue($channels));

        $customerRepository->expects($this->once())
            ->method('getGroupedByChannelArray')
            ->with($this->aclHelper)
            ->will($this->returnValue($sourceData));

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
            ->with('new_web_customers')
            ->will($this->returnValue($chartConfig));
        $this->dateHelper->expects($this->any())
            ->method('getFormatStrings')
            ->willReturn(
                ['viewType' => 'month']
            );
        $this->dateHelper->expects($this->once())
            ->method('getDatePeriod')
            ->willReturnCallback(function ($past, $now) {
                return [
                    '2014-02' => ['date' => '2014-02-01'],
                    '2014-03' => ['date' => '2014-03-01'],
                    '2014-04' => ['date' => '2014-04-01'],
                    '2014-05' => ['date' => '2014-05-01'],
                    '2014-06' => ['date' => '2014-06-01'],
                    '2014-07' => ['date' => '2014-07-01'],
                    '2014-08' => ['date' => '2014-08-01'],
                    '2014-09' => ['date' => '2014-09-01'],
                    '2014-10' => ['date' => '2014-10-01'],
                    '2014-11' => ['date' => '2014-11-01'],
                    '2014-12' => ['date' => '2014-12-01'],
                ];
            });
        $this->dateHelper->expects($this->any())
            ->method('getKey')
            ->willReturnCallback(function ($past, $now, $row) {
                return $row['yearCreated'] . '-' . $row['monthCreated'];
            });
        $this->dateHelper->expects($this->once())
            ->method('getPeriod')
            ->willReturnCallback(function ($dateRange) {
                return [$dateRange['start'], $dateRange['end']];
            });
        $this->assertEquals(
            $chartView,
            $this->dataProvider->getNewCustomerChartView($chartViewBuilder, $dateRange)
        );
    }

    /**
     * @return array
     */
    public function getNewCustomerChartViewDataProvider()
    {
        $ulcTimezone = new \DateTimeZone('UTC');
        $now  = new \DateTime('2015-01-01', $ulcTimezone);
        $past = clone $now;
        $past = $past->sub(new \DateInterval("P11M"));
        $past = \DateTime::createFromFormat('Y-m-d', $past->format('Y-m-01'), $ulcTimezone);

        $past->setTime(0, 0, 0);

        $datePeriod = new \DatePeriod($past, new \DateInterval('P1M'), $now);
        $dates      = [];

        $nowClone = clone $now;
        $nowClone = $nowClone->sub(new \DateInterval("P1M"));
        $nowMonth = $nowClone->format('Y-m');

        // create dates by date period
        /** @var \DateTime $dt */
        foreach ($datePeriod as $dt) {
            $key = $dt->format('Y-m');
            $dates[$key] = [
                'date' => sprintf('%s-01', $key),
            ];
        }

        $expected = [];

        $firstDates = $dates;
        $firstDates[$nowMonth]['cnt'] = 16;
        $expected['First'] = array_values($firstDates);

        $secondDates = $dates;
        $secondDates[$nowMonth]['cnt'] = 12;
        $expected['Second'] = array_values($secondDates);

        return [
            [
                'channels' => [
                    3 => [
                        'name' => 'First',
                    ],
                    4 => [
                        'name' => 'Second',
                    ]
                ],
                'sourceData' => [
                    [
                        'channelId'    => 3,
                        'cnt'          => 16,
                        'yearCreated'  => '2014',
                        'monthCreated' => '12',
                    ],
                    [
                        'channelId'    => 4,
                        'cnt'          => 12,
                        'yearCreated'  => '2014',
                        'monthCreated' => '12',
                    ]
                ],
                'expectedArrayData' => $expected,
                'expectedOptions' => [
                    'name'        => 'multiline_chart',
                    'data_schema' => [
                        'label' => [
                            'field_name' => 'month_year',
                            'label'      => 'oro.dashboard.chart.month.label',
                            'type'       => 'month'
                        ],
                        'value' => [
                            'field_name' => 'cnt',
                            'label'      => 'oro.magento.dashboard.new_magento_customers_chart.customer_count',
                        ],
                    ],
                ],
                'chartConfig' => [
                    'data_schema' => [
                        'label' => [
                            'field_name' => 'month_year',
                            'label'      => 'oro.dashboard.chart.month.label',
                            'type'       => 'month',
                        ],
                        'value' => [
                            'field_name' => 'cnt',
                            'label'      => 'oro.magento.dashboard.new_magento_customers_chart.customer_count'
                        ]
                    ]
                ],
                'dateRange' => [
                    'start' => $past,
                    'end' => $now
                ]
            ]
        ];
    }
}
