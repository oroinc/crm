<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Converter;

use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use OroCRM\Bundle\MagentoBundle\Dashboard\OrderDataProvider;

class OrderDataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $aclHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configProvider;

    /**
     * @var OrderDataProvider
     */
    protected $dataProvider;

    protected function setUp()
    {
        $this->registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->aclHelper = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configProvider = $this->getMockBuilder('Oro\Bundle\ChartBundle\Model\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataProvider = new OrderDataProvider(
            $this->registry,
            $this->aclHelper,
            $this->configProvider
        );
    }

    public function testGetAverageOrderAmountByCustomerChartView()
    {
        $sourceOrderData = [
            'First' => [
                ['month' => '2014-09-01', 'amount' => 3],
            ],
            'Second' => [
                ['month' => '2014-09-01', 'amount' => 5],
            ]
        ];
        $expectedArrayData = [
            'First' => [
                ['month' => '2014-09-01', 'amount' => 3],
            ],
            'Second' => [
                ['month' => '2014-09-01', 'amount' => 5],
            ]
        ];
        $expectedOptions = [
            'name' => 'multiline_chart',
            'data_schema' => [
                'label' => [
                    'field_name' => 'month',
                    'label' => 'oro.dashboard.chart.month.label',
                    'type' => 'month',
                ],
                'value' => [
                    'field_name' => 'amount',
                    'label' => 'orocrm.magento.dashboard.average_order_amount_chart.order_amount',
                    'type' => 'currency',
                ],
            ],
        ];
        $chartConfig = [
            'data_schema' => [
                'label' => [
                    'field_name' => 'month',
                    'label' => 'orocrm.magento.dashboard.average_order_amount_chart.month',
                    'type' => 'month',
                ],
                'value' => [
                    'field_name' => 'amount',
                    'label' => 'orocrm.magento.dashboard.average_order_amount_chart.order_amount',
                    'type' => 'currency'
                ]
            ]
        ];

        $orderRepository = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\Repository\OrderRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $start = new \DateTime('2012-01-01');
        $end = new \DateTime('2015-01-01');
        $dateHelper = new DateHelper();
        $orderRepository->expects($this->once())
            ->method('getAverageOrderAmount')
            ->with($this->aclHelper, $start, $end, $dateHelper)
            ->will($this->returnValue($sourceOrderData));

        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroCRMMagentoBundle:Order')
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
                [
                    'start' => $start,
                    'end' => $end
                ],
                $dateHelper
            )
        );
    }
}
