<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Converter;

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
    protected $translator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $aclHelper;

    /**
     * @var OrderDataProvider
     */
    protected $dataProvider;

    protected function setUp()
    {
        $this->registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $this->aclHelper = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->translator->expects($this->any())
            ->method('trans')
            ->will(
                $this->returnCallback(
                    function ($id) {
                        return $id . '.trans';
                    }
                )
            );

        $this->dataProvider = new OrderDataProvider($this->registry, $this->translator, $this->aclHelper);
    }

    public function testGetAverageOrderAmountByCustomerChartView()
    {
        $sourceOrderData = [
            1 => [
                'name' => 'First',
                'data' => [2014 => [9 => 3]],
            ],
            2 => [
                'name' => 'Second',
                'data' => [2014 => [9 => 5]],
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
                    'label' => null,
                    'type' => 'month'
                ],
                'value' => [
                    'field_name' => 'amount',
                    'label' => 'orocrm.magento.dashboard.average_order_amount_by_customer_chart.order_amount.trans'
                ],
            ],
        ];

        $orderRepository = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\Repository\OrderRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orderRepository->expects($this->once())
            ->method('getAverageOrdersByCustomers')
            ->with($this->aclHelper)
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

        $this->assertEquals(
            $chartView,
            $this->dataProvider->getAverageOrderAmountByCustomerChartView($chartViewBuilder)
        );
    }
}
