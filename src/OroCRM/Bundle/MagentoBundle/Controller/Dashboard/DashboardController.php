<?php

namespace OroCRM\Bundle\MagentoBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
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
        $dateTo = new \DateTime('now', new \DateTimeZone('UTC'));
        $dateFrom = new \DateTime(
            $dateTo->format('Y') . '-01-' . ((ceil($dateTo->format('n') / 3) - 1) * 3 + 1),
            new \DateTimeZone('UTC')
        );

        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->get('oro_workflow.manager');
        $workflow = $workflowManager->getApplicableWorkflowByEntityClass(
            'OroCRM\Bundle\MagentoBundle\Entity\Cart'
        );

        /** @var CartRepository $shoppingCartRepository */
        $shoppingCartRepository = $this->getDoctrine()->getRepository('OroCRMMagentoBundle:Cart');

        $data = $shoppingCartRepository->getFunnelChartData(
            $dateFrom,
            $dateTo,
            $workflow,
            $this->get('oro_security.acl_helper')
        );

        $widgetAttr = $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
            ->setArrayData($data)
            ->setOptions(
                array(
                    'name' => 'flow_chart',
                    'settings' => array('quarterDate' => $dateFrom),
                    'data_schema' => array(
                        'label' => array('field_name' => 'label'),
                        'value' => array('field_name' => 'value'),
                        'isNozzle' => array('field_name' => 'isNozzle'),
                    )
                )
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
        $widgetAttributes = $this->get('oro_dashboard.widget_configs');
        $orderDataProvider = $this->get('orocrm_magento.dashboard.data_provider.order');
        $chartViewBuilder = $this->get('oro_chart.view_builder');

        $data = $widgetAttributes->getWidgetAttributesForTwig('average_order_amount_chart');
        $data['chartView'] = $orderDataProvider->getAverageOrderAmountChartView($chartViewBuilder);

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

        $data = $widgetAttributes->getWidgetAttributesForTwig('new_magento_customers_chart');
        $data['chartView'] = $customerDataProvider->getNewCustomerChartView($chartViewBuilder);

        return $data;
    }
}
