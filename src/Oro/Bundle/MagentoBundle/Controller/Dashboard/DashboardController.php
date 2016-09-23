<?php

namespace Oro\Bundle\MagentoBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\DashboardBundle\Model\WidgetConfigs;
use Oro\Bundle\DashboardBundle\Provider\Converters\FilterDateRangeConverter;
use Oro\Bundle\WorkflowBundle\Model\WorkflowAwareManager;
use Oro\Bundle\MagentoBundle\Dashboard\OrderDataProvider;
use Oro\Bundle\MagentoBundle\Dashboard\PurchaseDataProvider;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Repository\CartRepository;

class DashboardController extends Controller
{
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
        $dateRange = $this->get('oro_dashboard.widget_configs')
            ->getWidgetOptions($request->query->get('_widgetId', null))
            ->get('dateRange');

        $dateTo   = $dateRange['end'];
        $dateFrom = $dateRange['start'];

        /** @var WorkflowAwareManager $workflowManager */
        $workflowManager = $this->get('oro_magento.manager.abandoned_shopping_cart_flow');
        $workflow        = $workflowManager->getWorkflow();

        /** @var CartRepository $shoppingCartRepository */
        $shoppingCartRepository = $this->getDoctrine()->getRepository('OroMagentoBundle:Cart');

        $data = $shoppingCartRepository->getFunnelChartData(
            $dateFrom,
            $dateTo,
            $workflow,
            $this->get('oro_security.acl_helper')
        );

        $widgetAttr = $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig($widget);
        if (!$dateFrom) {
            $dateFrom = new \DateTime(FilterDateRangeConverter::MIN_DATE, new \DateTimeZone('UTC'));
        }
        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
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
        $widgetAttributes  = $this->get('oro_dashboard.widget_configs');
        $orderDataProvider = $this->get('oro_magento.dashboard.data_provider.order');
        $chartViewBuilder  = $this->get('oro_chart.view_builder');

        $data              = $widgetAttributes->getWidgetAttributesForTwig('average_order_amount_chart');
        $data['chartView'] = $orderDataProvider->getAverageOrderAmountChartView(
            $chartViewBuilder,
            $this->get('oro_dashboard.widget_configs')
                ->getWidgetOptions($request->query->get('_widgetId', null))
                ->get('dateRange'),
            $this->get('oro_dashboard.datetime.helper')
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
        $widgetAttributes     = $this->get('oro_dashboard.widget_configs');
        $customerDataProvider = $this->get('oro_magento.dashboard.data_provider.customer');
        $chartViewBuilder     = $this->get('oro_chart.view_builder');

        $data              = $widgetAttributes->getWidgetAttributesForTwig('new_magento_customers_chart');
        $data['chartView'] = $customerDataProvider->getNewCustomerChartView(
            $chartViewBuilder,
            $this->get('oro_dashboard.widget_configs')
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
        $widgetAttributes     = $this->getWidgetConfigs();
        $purchaseDataProvider = $this->getPurchaseDataProvider();
        $chartViewBuilder     = $this->getChartViewBuilder();

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
        $widgetAttributes  = $this->getWidgetConfigs();
        $orderDataProvider = $this->getOrderDataProvider();
        $chartViewBuilder  = $this->getChartViewBuilder();

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
        $widgetAttributes  = $this->getWidgetConfigs();
        $orderDataProvider = $this->getOrderDataProvider();
        $chartViewBuilder  = $this->getChartViewBuilder();

        $data              = $widgetAttributes->getWidgetAttributesForTwig('orders_over_time_chart');
        $data['chartView'] = $orderDataProvider->getOrdersOverTimeChartView(
            $chartViewBuilder,
            $widgetAttributes
                ->getWidgetOptions()
                ->get('dateRange')
        );

        return $data;
    }

    /**
     * @return ChartViewBuilder
     */
    protected function getChartViewBuilder()
    {
        return $this->get('oro_chart.view_builder');
    }

    /**
     * @return WidgetConfigs
     */
    protected function getWidgetConfigs()
    {
        return $this->get('oro_dashboard.widget_configs');
    }

    /**
     * @return OrderDataProvider
     */
    protected function getOrderDataProvider()
    {
        return $this->get('oro_magento.dashboard.data_provider.order');
    }

    /**
     * @return PurchaseDataProvider
     */
    public function getPurchaseDataProvider()
    {
        return $this->get('oro_magento.dashboard.data_provider.purchase');
    }
}
