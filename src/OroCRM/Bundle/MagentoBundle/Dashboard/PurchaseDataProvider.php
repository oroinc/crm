<?php

namespace OroCRM\Bundle\MagentoBundle\Dashboard;

use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NoResultException;

use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\ChartBundle\Model\ChartView;
use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\ChartBundle\Model\ConfigProvider;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use OroCRM\Bundle\MagentoBundle\Entity\Repository\CartRepository;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\OrderRepository;
use OroCRM\Bundle\MagentoBundle\Provider\TrackingVisitProvider;

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
     * @var AclHelper
     */
    protected $aclHelper;

    /** @var DateFilterProcessor */
    protected $dateFilterProcessor;

    /**
     * @param ManagerRegistry       $registry
     * @param ConfigProvider        $configProvider
     * @param TrackingVisitProvider $trackingVisitProvider
     * @param TranslatorInterface   $translator
     * @param AclHelper             $aclHelper
     * @param DateFilterProcessor   $processor
     */
    public function __construct(
        ManagerRegistry $registry,
        ConfigProvider $configProvider,
        TrackingVisitProvider $trackingVisitProvider,
        TranslatorInterface $translator,
        AclHelper $aclHelper,
        DateFilterProcessor $processor
    ) {
        $this->registry = $registry;
        $this->configProvider = $configProvider;
        $this->trackingVisitProvider = $trackingVisitProvider;
        $this->translator = $translator;
        $this->aclHelper = $aclHelper;
        $this->dateFilterProcessor = $processor;
    }

    /**
     * @param ChartViewBuilder $viewBuilder
     *
     * @return ChartView
     */
    public function getPurchaseChartView(ChartViewBuilder $viewBuilder, array $dateRange)
    {
        try {
            $visitedCountQB = $this->trackingVisitProvider->getVisitedCountQB('t');
            $this->dateFilterProcessor->process($visitedCountQB, $dateRange, 't.firstActionTime');

            $visitCount =  (int)$this->aclHelper->apply($visitedCountQB)->getSingleScalarResult();
        } catch (NoResultException $ex) {
            $visitCount = 0;
        }
        try {
            $deeplyVisitedCountQB = $this->trackingVisitProvider->getDeeplyVisitedCountQB('t1');
            $this->dateFilterProcessor->process($deeplyVisitedCountQB, $dateRange, 't1.firstActionTime');
            $deeplyVisitedCount = (int)$this->aclHelper->apply($deeplyVisitedCountQB)->getSingleScalarResult();
        } catch (NoResultException $ex) {
            $deeplyVisitedCount = 0;
        }
        try {
            $customersCountQB = $this->getCartRepository()->getCustomersCountWhatMakeCartsQB('c');
            $this->dateFilterProcessor->process($customersCountQB, $dateRange, 'c.createdAt');
            $customersCount = (int)$this->aclHelper->apply($customersCountQB)->getSingleScalarResult();
        } catch (NoResultException $ex) {
            $customersCount = 0;
        }
        try {
            $uniqueCustomersQB = $this->getOrderRepository()->getUniqueBuyersCountQB('b');
            $this->dateFilterProcessor->process($uniqueCustomersQB, $dateRange, 'b.createdAt');
            $uniqueCustomersCount = (int)$this->aclHelper->apply($uniqueCustomersQB)->getSingleScalarResult();
        } catch (NoResultException $ex) {
            $uniqueCustomersCount = 0;
        }
        $items = [
            [
                'label'    => $this->translator->trans('orocrm.magento.dashboard.purchase_chart.visited'),
                'value'    => $visitCount,
                'isNozzle' => false
            ],
            [
                'label'    => $this->translator->trans('orocrm.magento.dashboard.purchase_chart.deeply_visited'),
                'value'    => $deeplyVisitedCount,
                'isNozzle' => false
            ],
            [
                'label'    => $this->translator->trans('orocrm.magento.dashboard.purchase_chart.added_to_cart'),
                'value'    => $customersCount,
                'isNozzle' => false
            ],
            [
                'label'    => $this->translator->trans('orocrm.magento.dashboard.purchase_chart.purchased'),
                'value'    => $uniqueCustomersCount,
                'isNozzle' => true
            ]
        ];

        $chartOptions = array_merge_recursive(
            [
                'name' => 'flow_chart',
                'settings' => [
                    'quarterDate' => $dateRange['start']
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
