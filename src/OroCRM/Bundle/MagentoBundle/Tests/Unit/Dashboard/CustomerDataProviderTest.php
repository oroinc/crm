<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Converter;

use OroCRM\Bundle\MagentoBundle\Dashboard\CustomerDataProvider;

class CustomerDataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var CustomerDataProvider
     */
    protected $dataProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $aclHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configProvider;

    protected function setUp()
    {
        $this->registry   = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->aclHelper  = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configProvider = $this->getMockBuilder('Oro\Bundle\ChartBundle\Model\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataProvider = new CustomerDataProvider(
            $this->registry,
            $this->aclHelper,
            $this->configProvider
        );
    }

    /**
     * @param array $channels
     * @param array $sourceData
     * @param array $expectedArrayData
     * @param array $expectedOptions
     * @param array $chartConfig
     * @dataProvider getNewCustomerChartViewDataProvider
     */
    public function testGetNewCustomerChartView(
        array $channels,
        array $sourceData,
        array $expectedArrayData,
        array $expectedOptions,
        array $chartConfig
    ) {
        $channelRepository = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Entity\Repository\ChannelRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $customerRepository = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\Repository\CustomerRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry->expects($this->any())
            ->method('getRepository')
            ->will(
                $this->returnCallback(
                    function ($entityName) use ($channelRepository, $customerRepository) {
                        if ($entityName == 'OroCRMChannelBundle:Channel') {
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

        $this->assertEquals(
            $chartView,
            $this->dataProvider->getNewCustomerChartView($chartViewBuilder)
        );
    }

    /**
     * @return array
     */
    public function getNewCustomerChartViewDataProvider()
    {
        $ulcTimezone = new \DateTimeZone('UTC');
        $now  = new \DateTime('now', $ulcTimezone);
        $past = clone $now;
        $past = $past->sub(new \DateInterval("P11M"));
        $past = \DateTime::createFromFormat('Y-m-d', $past->format('Y-m-01'), $ulcTimezone);

        $past->setTime(0, 0, 0);

        $datePeriod = new \DatePeriod($past, new \DateInterval('P1M'), $now);
        $dates      = [];

        $nowMonth = $now->format('Y-m');

        // create dates by date period
        /** @var \DateTime $dt */
        foreach ($datePeriod as $dt) {
            $key = $dt->format('Y-m');
            $dates[$key] = array(
                'month_year' => sprintf('%s-01', $key),
                'cnt'        => 0
            );
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
                        'yearCreated'  => $now->format('Y'),
                        'monthCreated' => $now->format('m'),
                    ],
                    [
                        'channelId'    => 4,
                        'cnt'          => 12,
                        'yearCreated'  => $now->format('Y'),
                        'monthCreated' => $now->format('m'),
                    ]
                ],
                'expectedArrayData' => $expected,
                'expectedOptions' => [
                    'name'        => 'multiline_chart',
                    'data_schema' => [
                        'label' => [
                            'field_name' => 'month_year',
                            'label'      => 'orocrm.magento.dashboard.new_magento_customers_chart.month',
                            'type'       => 'month'
                        ],
                        'value' => [
                            'field_name' => 'cnt',
                            'label'      => 'orocrm.magento.dashboard.new_magento_customers_chart.customer_count',
                        ],
                    ],
                ],
                'chartConfig' => [
                    'data_schema' => [
                        'label' => [
                            'field_name' => 'month_year',
                            'label'      => 'orocrm.magento.dashboard.new_magento_customers_chart.month',
                            'type'       => 'month',
                        ],
                        'value' => [
                            'field_name' => 'cnt',
                            'label'      => 'orocrm.magento.dashboard.new_magento_customers_chart.customer_count'
                        ]
                    ]
                ]
            ]
        ];
    }
}
