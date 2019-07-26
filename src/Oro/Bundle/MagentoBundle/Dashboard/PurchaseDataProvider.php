<?php

namespace Oro\Bundle\MagentoBundle\Dashboard;

use DateTime;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\ChartBundle\Model\ChartView;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\ChartBundle\Model\ConfigProvider;
use Oro\Bundle\DashboardBundle\Provider\Converters\FilterDateRangeConverter;
use Oro\Bundle\MagentoBundle\Entity\Repository\CartRepository;
use Oro\Bundle\MagentoBundle\Entity\Repository\OrderRepository;
use Oro\Bundle\MagentoBundle\Provider\TrackingVisitProviderInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @var TrackingVisitProviderInterface
     */
    protected $trackingVisitProvider;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var AclHelper
     */
    protected $aclHelper;

    /**
     * @param ManagerRegistry                $registry
     * @param ConfigProvider                 $configProvider
     * @param TrackingVisitProviderInterface $trackingVisitProvider
     * @param TranslatorInterface            $translator
     * @param AclHelper                      $aclHelper
     */
    public function __construct(
        ManagerRegistry $registry,
        ConfigProvider $configProvider,
        TrackingVisitProviderInterface $trackingVisitProvider,
        TranslatorInterface $translator,
        AclHelper $aclHelper
    ) {
        $this->registry              = $registry;
        $this->configProvider        = $configProvider;
        $this->trackingVisitProvider = $trackingVisitProvider;
        $this->translator            = $translator;
        $this->aclHelper             = $aclHelper;
    }

    /**
     * @param ChartViewBuilder $viewBuilder
     * @param DateTime         $from
     * @param DateTime         $to
     *
     * @return ChartView
     */
    public function getPurchaseChartView(ChartViewBuilder $viewBuilder, DateTime $from = null, DateTime $to = null)
    {
        $items = [
            [
                'label'    => $this->translator->trans('oro.magento.dashboard.purchase_chart.visited'),
                'value'    => $this->trackingVisitProvider->getVisitedCount($from, $to),
                'isNozzle' => false
            ],
            [
                'label'    => $this->translator->trans('oro.magento.dashboard.purchase_chart.deeply_visited'),
                'value'    => $this->trackingVisitProvider->getDeeplyVisitedCount($from, $to),
                'isNozzle' => false
            ],
            [
                'label'    => $this->translator->trans('oro.magento.dashboard.purchase_chart.added_to_cart'),
                'value'    => $this->getCartRepository()->getCustomersCountWhatMakeCarts($this->aclHelper, $from, $to),
                'isNozzle' => false
            ],
            [
                'label'    => $this->translator->trans('oro.magento.dashboard.purchase_chart.purchased'),
                'value'    => $this->getOrderRepository()->getUniqueBuyersCount($this->aclHelper, $from, $to),
                'isNozzle' => true
            ]
        ];

        if (!$from) {
            $from = new \DateTime(FilterDateRangeConverter::MIN_DATE, new \DateTimeZone('UTC'));
        }
        $chartOptions = array_merge_recursive(
            [
                'name'     => 'flow_chart',
                'settings' => [
                    'quarterDate' => $from
                ]
            ],
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
        return $this->registry->getRepository('OroMagentoBundle:Cart');
    }

    /**
     * @return OrderRepository
     */
    protected function getOrderRepository()
    {
        return $this->registry->getRepository('OroMagentoBundle:Order');
    }
}
