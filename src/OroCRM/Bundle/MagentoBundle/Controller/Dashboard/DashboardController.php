<?php

namespace OroCRM\Bundle\MagentoBundle\Controller\Dashboard;

use Oro\Bundle\WorkflowBundle\Model\WorkflowAwareManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\DashboardBundle\Model\WidgetConfigs;
use Oro\Bundle\DashboardBundle\Provider\Converters\FilterDateRangeConverter;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use OroCRM\Bundle\MagentoBundle\Dashboard\OrderDataProvider;
use OroCRM\Bundle\MagentoBundle\Dashboard\PurchaseDataProvider;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\CartRepository;

class DashboardController extends Controller
{
    /**
     * @Route(
     *      "/sales_flow_b2c/chart/{widget}",
     *      name="orocrm_magento_dashboard_sales_flow_b2c_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroCRMSalesBundle:Dashboard:salesFlowChart.html.twig")
     */
    public function mySalesFlowB2CAction($widget)
    {
        $dateRange = $this->get('oro_dashboard.widget_configs')
            ->getWidgetOptions($this->getRequest()->query->get('_widgetId', null))
            ->get('dateRange');

        $dateTo   = $dateRange['end'];
        $dateFrom = $dateRange['start'];

        /** @var WorkflowAwareManager $workflowManager */
        $workflowManager = $this->get('orocrm_magento.manager.abandoned_shopping_cart_flow');
        $workflow        = $workflowManager->getWorkflow();

        /** @var CartRepository $shoppingCartRepository */
        $shoppingCartRepository = $this->getDoctrine()->getRepository('OroCRMMagentoBundle:Cart');

        $data = $shoppingCartRepository->getFunnelChartData(
            $dateFrom,
            $dateTo,
            $workflows ? reset($workflows) : null,
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
     *      name="orocrm_magento_dashboard_average_order_amount",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroCRMMagentoBundle:Dashboard:ordersByCustomers.html.twig")
     */
    public function averageOrderAmountAction()
    {
        $widgetAttributes  = $this->get('oro_dashboard.widget_configs');
        $orderDataProvider = $this->get('orocrm_magento.dashboard.data_provider.order');
        $chartViewBuilder  = $this->get('oro_chart.view_builder');

        $data              = $widgetAttributes->getWidgetAttributesForTwig('average_order_amount_chart');
        $data['chartView'] = $orderDataProvider->getAverageOrderAmountChartView(
            $chartViewBuilder,
            $this->get('oro_dashboard.widget_configs')
                ->getWidgetOptions($this->getRequest()->query->get('_widgetId', null))
                ->get('dateRange'),
            $this->get('oro_dashboard.datetime.helper')
        );

        return $data;
    }

    /**
     * @Route(
     *      "/orocrm_magento_dashboard_new_customers_chart",
     *      name="orocrm_magento_dashboard_new_customers_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroCRMMagentoBundle:Dashboard:newCustomersChart.html.twig")
     */
    public function newCustomersAction()
    {
        $widgetAttributes     = $this->get('oro_dashboard.widget_configs');
        $customerDataProvider = $this->get('orocrm_magento.dashboard.data_provider.customer');
        $chartViewBuilder     = $this->get('oro_chart.view_builder');

        $data              = $widgetAttributes->getWidgetAttributesForTwig('new_magento_customers_chart');
        $data['chartView'] = $customerDataProvider->getNewCustomerChartView(
            $chartViewBuilder,
            $this->get('oro_dashboard.widget_configs')
                ->getWidgetOptions($this->getRequest()->query->get('_widgetId', null))
                ->get('dateRange')
        );

        return $data;
    }

    /**
     * @Route(
     *      "/orocrm_magento_dashboard_purchase_chart",
     *      name="orocrm_magento_dashboard_purchase_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroCRMMagentoBundle:Dashboard:purchaseChart.html.twig")
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
     *      "/orocrm_magento_dashboard_revenue_over_time_chart",
     *      name="orocrm_magento_dashboard_revenue_over_time_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroCRMMagentoBundle:Dashboard:revenueOverTimeChart.html.twig")
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
     *      "/orocrm_magento_dashboard_orders_over_time_chart",
     *      name="orocrm_magento_dashboard_orders_over_time_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroCRMMagentoBundle:Dashboard:ordersOverTimeChart.html.twig")
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
        return $this->get('orocrm_magento.dashboard.data_provider.order');
    }

    /**
     * @return PurchaseDataProvider
     */
    public function getPurchaseDataProvider()
    {
        return $this->get('orocrm_magento.dashboard.data_provider.purchase');
    }
}
