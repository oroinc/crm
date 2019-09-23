<?php

namespace Oro\Bundle\MagentoBundle\Controller\Dashboard;

use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Oro\Bundle\DashboardBundle\Model\WidgetConfigs;
use Oro\Bundle\DashboardBundle\Provider\Converters\FilterDateRangeConverter;
use Oro\Bundle\MagentoBundle\Dashboard\CustomerDataProvider;
use Oro\Bundle\MagentoBundle\Dashboard\OrderDataProvider;
use Oro\Bundle\MagentoBundle\Dashboard\PurchaseDataProvider;
use Oro\Bundle\MagentoBundle\Entity\Repository\CartRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\WorkflowBundle\Helper\WorkflowTranslationHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowAwareManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Adds action which are responsible for rendering magento dashboard chart widgets
 */
class DashboardController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            WidgetConfigs::class,
            WorkflowAwareManager::class,
            TranslatorInterface::class,
            AclHelper::class,
            ChartViewBuilder::class,
            OrderDataProvider::class,
            DateHelper::class,
            CustomerDataProvider::class,
            PurchaseDataProvider::class
        ]);
    }

    /**
     * @Route(
     *      "/sales_flow_b2c/chart/{widget}",
     *      name="oro_magento_dashboard_sales_flow_b2c_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroSalesBundle:Dashboard:salesFlowChart.html.twig")
     *
     * @param Request $request
     * @param $widget
     *
     * @return array
     */
    public function mySalesFlowB2CAction(Request $request, $widget)
    {
        $widgetConfigs = $this->get(WidgetConfigs::class);
        $dateRange = $widgetConfigs
            ->getWidgetOptions($request->query->get('_widgetId', null))
            ->get('dateRange');

        $dateTo   = $dateRange['end'];
        $dateFrom = $dateRange['start'];

        /** @var WorkflowAwareManager $workflowManager */
        $workflowManager = $this->get(WorkflowAwareManager::class);
        $workflow        = $workflowManager->getWorkflow();

        /** @var CartRepository $shoppingCartRepository */
        $shoppingCartRepository = $this->getDoctrine()->getRepository('OroMagentoBundle:Cart');

        $data = $shoppingCartRepository->getFunnelChartData(
            $dateFrom,
            $dateTo,
            $workflow,
            $this->get(AclHelper::class)
        );
        $translator = $this->get(TranslatorInterface::class);

        foreach ($data as &$item) {
            $item['label'] = $translator->trans($item['label'], [], WorkflowTranslationHelper::TRANSLATION_DOMAIN);
        }

        $widgetAttr = $widgetConfigs->getWidgetAttributesForTwig($widget);
        if (!$dateFrom) {
            $dateFrom = new \DateTime(FilterDateRangeConverter::MIN_DATE, new \DateTimeZone('UTC'));
        }
        $widgetAttr['chartView'] = $this->get(ChartViewBuilder::class)
            ->setArrayData($data)
            ->setOptions(
                [
                    'name'        => 'flow_chart',
                    'settings'    => ['quarterDate' => $dateFrom],
                    'data_schema' => [
                        'label'    => ['field_name' => 'label'],
                        'value'    => ['field_name' => 'value'],
                        'isNozzle' => ['field_name' => 'isNozzle'],
                    ]
                ]
            )
            ->getView();

        return $widgetAttr;
    }

    /**
     * @Route(
     *      "/average_order_amount_by_customer",
     *      name="oro_magento_dashboard_average_order_amount",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroMagentoBundle:Dashboard:ordersByCustomers.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function averageOrderAmountAction(Request $request)
    {
        $widgetAttributes  = $this->get(WidgetConfigs::class);
        $orderDataProvider = $this->get(OrderDataProvider::class);
        $chartViewBuilder  = $this->get(ChartViewBuilder::class);

        $data              = $widgetAttributes->getWidgetAttributesForTwig('average_order_amount_chart');
        $data['chartView'] = $orderDataProvider->getAverageOrderAmountChartView(
            $chartViewBuilder,
            $widgetAttributes
                ->getWidgetOptions($request->query->get('_widgetId', null))
                ->get('dateRange'),
            $this->get(DateHelper::class)
        );

        return $data;
    }

    /**
     * @Route(
     *      "/oro_magento_dashboard_new_customers_chart",
     *      name="oro_magento_dashboard_new_customers_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroMagentoBundle:Dashboard:newCustomersChart.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function newCustomersAction(Request $request)
    {
        $widgetAttributes     = $this->get(WidgetConfigs::class);
        $customerDataProvider = $this->get(CustomerDataProvider::class);
        $chartViewBuilder     = $this->get(ChartViewBuilder::class);

        $data              = $widgetAttributes->getWidgetAttributesForTwig('new_magento_customers_chart');
        $data['chartView'] = $customerDataProvider->getNewCustomerChartView(
            $chartViewBuilder,
            $widgetAttributes
                ->getWidgetOptions($request->query->get('_widgetId', null))
                ->get('dateRange')
        );

        return $data;
    }

    /**
     * @Route(
     *      "/oro_magento_dashboard_purchase_chart",
     *      name="oro_magento_dashboard_purchase_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroMagentoBundle:Dashboard:purchaseChart.html.twig")
     */
    public function purchaseAction()
    {
        $widgetAttributes     = $this->get(WidgetConfigs::class);
        $purchaseDataProvider = $this->get(PurchaseDataProvider::class);
        $chartViewBuilder     = $this->get(ChartViewBuilder::class);

        $dateRange = $widgetAttributes->getWidgetOptions()->get('dateRange');
        $from      = $dateRange['start'];
        $to        = $dateRange['end'];

        $data              = $widgetAttributes->getWidgetAttributesForTwig('purchase_chart');
        $data['chartView'] = $purchaseDataProvider->getPurchaseChartView($chartViewBuilder, $from, $to);

        return $data;
    }

    /**
     * @Route(
     *      "/oro_magento_dashboard_revenue_over_time_chart",
     *      name="oro_magento_dashboard_revenue_over_time_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroMagentoBundle:Dashboard:revenueOverTimeChart.html.twig")
     */
    public function revenueOverTimeAction()
    {
        $widgetAttributes  = $this->get(WidgetConfigs::class);
        $orderDataProvider = $this->get(OrderDataProvider::class);
        $chartViewBuilder  = $this->get(ChartViewBuilder::class);

        $data              = $widgetAttributes->getWidgetAttributesForTwig('revenue_over_time_chart');
        $data['chartView'] = $orderDataProvider->getRevenueOverTimeChartView(
            $chartViewBuilder,
            $widgetAttributes
                ->getWidgetOptions()
                ->get('dateRange')
        );

        return $data;
    }

    /**
     * @Route(
     *      "/oro_magento_dashboard_orders_over_time_chart",
     *      name="oro_magento_dashboard_orders_over_time_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroMagentoBundle:Dashboard:ordersOverTimeChart.html.twig")
     */
    public function ordersOverTimeAction()
    {
        $widgetAttributes  = $this->get(WidgetConfigs::class);
        $orderDataProvider = $this->get(OrderDataProvider::class);
        $chartViewBuilder  = $this->get(ChartViewBuilder::class);

        $data              = $widgetAttributes->getWidgetAttributesForTwig('orders_over_time_chart');
        $data['chartView'] = $orderDataProvider->getOrdersOverTimeChartView(
            $chartViewBuilder,
            $widgetAttributes
                ->getWidgetOptions()
                ->get('dateRange')
        );

        return $data;
    }
}
