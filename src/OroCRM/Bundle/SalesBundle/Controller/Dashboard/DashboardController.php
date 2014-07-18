<?php

namespace OroCRM\Bundle\SalesBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\TranslatorInterface;

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
        /** @var TranslatorInterface $translator */
        $translator = $this->get('translator');

        $data = $this->getDoctrine()
            ->getRepository('OroCRMSalesBundle:Lead')
            ->getOpportunitiesByLeadSource($this->get('oro_security.acl_helper'));

        foreach ($data as &$sourceData) {
            if (!empty($sourceData['label'])) {
                $sourceData['label'] = $translator->trans($sourceData['label']);
            }
        }

        $widgetAttr = $this->get('oro_dashboard.widget_attributes')->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
            ->setArrayData($data)
            ->setOptions(
                array(
                    'name' => 'pie_chart',
                    'data_schema' => array(
                        'label' => array('field_name' => 'label'),
                        'value' => array('field_name' => 'itemCount')
                    )
                )
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
        $items = $this->getDoctrine()
            ->getRepository('OroCRMSalesBundle:Opportunity')
            ->getOpportunitiesByStatus($this->get('oro_security.acl_helper'));

        $widgetAttr = $this->get('oro_dashboard.widget_attributes')->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
            ->setArrayData($items)
            ->setOptions(
                array(
                    'name' => 'bar_chart',
                    'data_schema' => array(
                        'label' => array('field_name' => 'label'),
                        'value' => array(
                            'field_name' => 'budget',
                            'type' => 'currency',
                            'formatter' => 'formatCurrency'
                        )
                    ),
                    'settings' => array('xNoTicks' => 2),
                )
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
        $dateTo = new \DateTime('now', new \DateTimeZone('UTC'));
        $dateFrom = new \DateTime(
            $dateTo->format('Y') . '-01-' . ((ceil($dateTo->format('n') / 3) - 1) * 3 + 1),
            new \DateTimeZone('UTC')
        );

        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->get('oro_workflow.manager');
        $workflow = $workflowManager->getApplicableWorkflowByEntityClass(
            'OroCRM\Bundle\SalesBundle\Entity\SalesFunnel'
        );

        $customStepCalculations = array('won_opportunity' => 'opportunity.closeRevenue');

        /** @var SalesFunnelRepository $salesFunnerRepository */
        $salesFunnerRepository = $this->getDoctrine()->getRepository('OroCRMSalesBundle:SalesFunnel');

        $data = $salesFunnerRepository->getFunnelChartData(
            $dateFrom,
            $dateTo,
            $workflow,
            $customStepCalculations,
            $this->get('oro_security.acl_helper')
        );

        $widgetAttr = $this->get('oro_dashboard.widget_attributes')->getWidgetAttributesForTwig($widget);
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
}
