<?php

namespace OroCRM\Bundle\SalesBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use OroCRM\Bundle\SalesBundle\Entity\Repository\SalesFunnelRepository;

class DashboardController extends Controller
{
    /**
     * @Route(
     *      "/opportunities_by_lead_source/chart/{widget}",
     *      name="orocrm_sales_dashboard_opportunities_by_lead_source_chart",
     *      requirements={"widget"="[\w-]+"}
     * )
     * @Template("OroCRMSalesBundle:Dashboard:opportunitiesByLeadSource.html.twig")
     */
    public function opportunitiesByLeadSourceAction($widget)
    {
        $options = $this->get('oro_dashboard.widget_configs')->getWidgetOptions(
            $this->getRequest()->query->get('_widgetId', null)
        );

        // prepare chart data
        $dataProvider = $this->get('orocrm_sales.provider.opportunity_by_lead_source');

        $data = $dataProvider->getOpportunityByLeadSourceData(
            $options->get('dateRange', []),
            $this->get('oro_user.dashboard.owner_helper')->getOwnerIds($options),
            (bool) $options->get('byAmount', false)
        );

        $widgetAttr = $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
            ->setArrayData($data)
            ->setOptions(
                [
                    'name' => 'pie_chart',
                    'data_schema' => [
                        'label' => ['field_name' => 'source'],
                        'value' => ['field_name' => 'itemCount'],
                    ],
                ]
            )
            ->getView();

        return $widgetAttr;
    }

    /**
     * @Route(
     *      "/opportunity_state/chart/{widget}",
     *      name="orocrm_sales_dashboard_opportunity_by_state_chart",
     *      requirements={"widget"="[\w-]+"}
     * )
     * @Template("OroCRMSalesBundle:Dashboard:opportunityByStatus.html.twig")
     */
    public function opportunityByStatusAction($widget)
    {
        $options = $this->get('oro_dashboard.widget_configs')
            ->getWidgetOptions($this->getRequest()->query->get('_widgetId', null));
        if ($options->get('useQuantityAsData')) {
            $valueOptions = [
                'field_name' => 'quantity'
            ];
        } else {
            $valueOptions = [
                'field_name' => 'budget',
                'type'       => 'currency',
                'formatter'  => 'formatCurrency'
            ];
        }
        $items = $this->get('orocrm_sales.provider.opportunity_by_status')
            ->getOpportunitiesGroupedByStatus($options);
        $widgetAttr              = $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
            ->setArrayData($items)
            ->setOptions(
                [
                    'name'        => 'horizontal_bar_chart',
                    'data_schema' => [
                        'label' => ['field_name' => 'label'],
                        'value' => $valueOptions
                    ]
                ]
            )
            ->getView();

        return $widgetAttr;
    }

    /**
     * @Route(
     *      "/sales_flow_b2b/chart/{widget}",
     *      name="orocrm_sales_dashboard_sales_flow_b2b_chart",
     *      requirements={"widget"="[\w_-]+"}
     * )
     * @Template("OroCRMSalesBundle:Dashboard:salesFlowChart.html.twig")
     */
    public function mySalesFlowB2BAction($widget)
    {
        $dateRange = $this->get('oro_dashboard.widget_configs')
            ->getWidgetOptions($this->getRequest()->query->get('_widgetId', null))
            ->get('dateRange');

        $dateTo   = $dateRange['end'];
        $dateFrom = $dateRange['start'];

        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->get('oro_workflow.manager');
        $workflow        = $workflowManager->getApplicableWorkflowByEntityClass(
            'OroCRM\Bundle\SalesBundle\Entity\SalesFunnel'
        );

        $customStepCalculations = ['won_opportunity' => 'opportunity.closeRevenue'];

        /** @var SalesFunnelRepository $salesFunnerRepository */
        $salesFunnerRepository = $this->getDoctrine()->getRepository('OroCRMSalesBundle:SalesFunnel');

        $data = $salesFunnerRepository->getFunnelChartData(
            $dateFrom,
            $dateTo,
            $workflow,
            $customStepCalculations,
            $this->get('oro_security.acl_helper')
        );

        $widgetAttr              = $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig($widget);
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
}
