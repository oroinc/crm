<?php

namespace OroCRM\Bundle\MagentoBundle\Dashboard;

use DateTime;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\ChartBundle\Model\ConfigProvider;

use OroCRM\Bundle\MagentoBundle\Entity\Repository\CartRepository;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\OrderRepository;
use OroCRM\Bundle\MagentoBundle\Provider\TrackingVisitProvider;

use Symfony\Component\Translation\TranslatorInterface;

class PurchaseDataProvider
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var TrackingVisitProvider
     */
    protected $trackingVisitProvider;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param ManagerRegistry $registry
     * @param ConfigProvider $configProvider
     * @param TrackingVisitProvider $trackingVisitProvider
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ManagerRegistry $registry,
        ConfigProvider $configProvider,
        TrackingVisitProvider $trackingVisitProvider,
        TranslatorInterface $translator
    ) {
        $this->registry = $registry;
        $this->configProvider = $configProvider;
        $this->trackingVisitProvider = $trackingVisitProvider;
        $this->translator = $translator;
    }

    /**
     * @param ChartViewBuilder $viewBuilder
     * @param DateTime $from
     * @param DateTime $to
     *
     * @return ChartView
     */
    public function getPurchaseChartView(ChartViewBuilder $viewBuilder, DateTime $from, DateTime $to)
    {
        $items = [
            [
                'label'    => $this->translator->trans('orocrm.magento.dashboard.purchase_chart.visited'),
                'value'    => $this->trackingVisitProvider->getVisitedCount($from, $to),
                'isNozzle' => false,
            ],
            [
                'label'    => $this->translator->trans('orocrm.magento.dashboard.purchase_chart.deeply_visited'),
                'value'    => $this->trackingVisitProvider->getDeeplyVisitedCount($from, $to),
                'isNozzle' => false,
            ],
            [
                'label'    => $this->translator->trans('orocrm.magento.dashboard.purchase_chart.added_to_cart'),
                'value'    => $this->getCartRepository()->getUniqueCustomerCarts($from, $to),
                'isNozzle' => false,
            ],
            [
                'label'    => $this->translator->trans('orocrm.magento.dashboard.purchase_chart.purchased'),
                'value'    => $this->getOrderRepository()->getUniqueCustomersOrdersCount($from, $to),
                'isNozzle' => false,
            ]
        ];

        $chartOptions = array_merge_recursive(
            ['name' => 'flow_chart'],
            $this->configProvider->getChartConfig('purchase_chart')
        );

        return $viewBuilder->setOptions($chartOptions)
            ->setArrayData($items)
            ->getView();
    }

    /**
     * @return CartRepository
     */
    protected function getCartRepository()
    {
        return $this->registry->getRepository('OroCRMMagentoBundle:Cart');
    }

    /**
     * @return OrderRepository
     */
    protected function getOrderRepository()
    {
        return $this->registry->getRepository('OroCRMMagentoBundle:Order');
    }
}
