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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translator;

    /**
     * @var CustomerDataProvider
     */
    protected $dataProvider;

    protected function setUp()
    {
        $this->registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        $this->translator->expects($this->any())
            ->method('trans')
            ->will(
                $this->returnCallback(
                    function ($id) {
                        return $id . '.trans';
                    }
                )
            );

        $this->dataProvider = new CustomerDataProvider($this->registry, $this->translator);
    }

    public function testGetNewCustomerChartView()
    {
        $expectedChannels = [
            3 => [
                'name' => 'First',
            ],
            4 => [
                'name' => 'Second',
            ]
        ];

        $sourceData = [
            1 => [
                1 => 3,
                'cnt' => 16,
                'data' => [2014 => [9 => 3]],
                'createdAt' => new \DateTime('now', new \DateTimeZone('UTC'))
            ],
            2 => [
                1 => 4,
                'cnt' => 12,
                'data' => [2014 => [9 => 5]],
                'createdAt' => new \DateTime('now', new \DateTimeZone('UTC'))
            ]
        ];
        $expectedArrayData = $this->getExpectedArrayData();
        $expectedOptions = [
            'name'        => 'multiline_chart',
            'data_schema' => [
                'label' => [
                    'field_name' => 'month_year',
                    'label'      => null,
                    'type'       => 'month'
                ],
                'value' => [
                    'field_name' => 'cnt',
                    'label'      => 'orocrm.magento.dashboard.new_magento_customers_chart.customer_count.trans',
                ],
            ],
        ];

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
            ->method('getByType')
            ->with('magento')
            ->will($this->returnValue($expectedChannels));

        $customerRepository->expects($this->once())
            ->method('getGroupedByChannelArray')
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

        $this->assertEquals(
            $chartView,
            $this->dataProvider->getNewCustomerChartView($chartViewBuilder)
        );
    }

    protected function getExpectedArrayData()
    {
        return [
            'First' => [
                ['month_year' => '2013-09-01', 'cnt' => 0],
                ['month_year' => '2013-10-01', 'cnt' => 0],
                ['month_year' => '2013-11-01', 'cnt' => 0],
                ['month_year' => '2013-12-01', 'cnt' => 0],
                ['month_year' => '2014-01-01', 'cnt' => 0],
                ['month_year' => '2014-02-01', 'cnt' => 0],
                ['month_year' => '2014-03-01', 'cnt' => 0],
                ['month_year' => '2014-04-01', 'cnt' => 0],
                ['month_year' => '2014-05-01', 'cnt' => 0],
                ['month_year' => '2014-06-01', 'cnt' => 0],
                ['month_year' => '2014-07-01', 'cnt' => 0],
                ['month_year' => '2014-08-01', 'cnt' => 0],
                ['month_year' => '2014-09-01', 'cnt' => 16],
            ],
            'Second' => [
                ['month_year' => '2013-09-01', 'cnt' => 0],
                ['month_year' => '2013-10-01', 'cnt' => 0],
                ['month_year' => '2013-11-01', 'cnt' => 0],
                ['month_year' => '2013-12-01', 'cnt' => 0],
                ['month_year' => '2014-01-01', 'cnt' => 0],
                ['month_year' => '2014-02-01', 'cnt' => 0],
                ['month_year' => '2014-03-01', 'cnt' => 0],
                ['month_year' => '2014-04-01', 'cnt' => 0],
                ['month_year' => '2014-05-01', 'cnt' => 0],
                ['month_year' => '2014-06-01', 'cnt' => 0],
                ['month_year' => '2014-07-01', 'cnt' => 0],
                ['month_year' => '2014-08-01', 'cnt' => 0],
                ['month_year' => '2014-09-01', 'cnt' => 12],
            ]
        ];
    }
}
