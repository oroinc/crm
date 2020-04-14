<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Dashboard;

use DateTime;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\ChartBundle\Model\ConfigProvider;
use Oro\Bundle\MagentoBundle\Dashboard\PurchaseDataProvider;
use Oro\Bundle\MagentoBundle\Provider\TrackingVisitProvider;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class PurchaseDataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var TrackingVisitProvider
     */
    private $trackingVisitProvider;

    /**
     * @var PurchaseDataProvider
     */
    private $dataProvider;

    /**
     * @var AclHelper
     */
    private $aclHelper;

    protected function setUp(): void
    {
        $this->registry = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');

        $this->configProvider = $this->getMockBuilder('Oro\Bundle\ChartBundle\Model\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->trackingVisitProvider =
            $this->getMockBuilder('Oro\Bundle\MagentoBundle\Provider\TrackingVisitProviderInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this->createMock('Symfony\Contracts\Translation\TranslatorInterface');
        $translator->expects($this->any())
            ->method('trans')
            ->will($this->returnCallback(function ($id) {
                return $id;
            }));

        $this->aclHelper = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataProvider = new PurchaseDataProvider(
            $this->registry,
            $this->configProvider,
            $this->trackingVisitProvider,
            $translator,
            $this->aclHelper
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetPurchaseChartView()
    {
        $from = new DateTime();
        $expectedArrayData = [
            [
                'label'    => 'oro.magento.dashboard.purchase_chart.visited',
                'value'    => 10,
                'isNozzle' => false,
            ],
            [
                'label'    => 'oro.magento.dashboard.purchase_chart.deeply_visited',
                'value'    => 5,
                'isNozzle' => false,
            ],
            [
                'label'    => 'oro.magento.dashboard.purchase_chart.added_to_cart',
                'value'    => 30,
                'isNozzle' => false,
            ],
            [
                'label'    => 'oro.magento.dashboard.purchase_chart.purchased',
                'value'    => 13,
                'isNozzle' => true,
            ]
        ];
        $expectedOptions = [
            'name' => 'flow_chart',
            'settings' => [
                'quarterDate' => $from,
            ],
            'data_schema' => [
                'label' => [
                    'field_name' => 'label',
                    'label' => null,
                    'type' => 'string',
                ],
                'value' => [
                    'field_name' => 'value',
                    'label' => null,
                    'type' => 'integer',
                ],
                'isNozzle' => [
                    'field_name' => 'isNozzle',
                    'label' => null,
                    'type' => 'boolean',
                ]
            ],
        ];
        $chartConfig = ['data_schema' => $expectedOptions['data_schema']];
        $this->trackingVisitProvider
            ->expects($this->once())
            ->method('getVisitedCount')
            ->will($this->returnValue(10));
        $this->trackingVisitProvider
            ->expects($this->once())
            ->method('getDeeplyVisitedCount')
            ->will($this->returnValue(5));
        $cartRepository = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\Repository\CartRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $cartRepository->expects($this->once())
            ->method('getCustomersCountWhatMakeCarts')
            ->will($this->returnValue(30));
        $this->registry->expects($this->at(0))
            ->method('getRepository')
            ->with('OroMagentoBundle:Cart')
            ->will($this->returnValue($cartRepository));
        $orderRepository = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\Repository\OrderRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orderRepository->expects($this->once())
            ->method('getUniqueBuyersCount')
            ->will($this->returnValue(13));
        $this->registry->expects($this->at(1))
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
            ->with('purchase_chart')
            ->will($this->returnValue($chartConfig));
        $this->dataProvider->getPurchaseChartView($chartViewBuilder, $from, new DateTime());
    }
}
