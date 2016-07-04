<?php

namespace OroCRM\Bundle\SalesBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Translation\TranslatorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\EntityExtendBundle\Twig\EnumExtension;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use OroCRM\Bundle\SalesBundle\Entity\Repository\SalesFunnelRepository;
use OroCRM\Bundle\SalesBundle\Entity\SalesFunnel;

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

        /** @var EnumExtension $enumValueTranslator */
        $enumValueTranslator = $this->get('oro_entity_extend.twig.extension.enum');

        $options = $this->get('oro_dashboard.widget_configs')->getWidgetOptions(
            $this->getRequest()->query->get('_widgetId', null)
        );
        $data = $this->getDoctrine()
            ->getRepository('OroCRMSalesBundle:Lead')
            ->getOpportunitiesByLeadSource(
                $this->get('oro_security.acl_helper'),
                10,
                $options->get('dateRange'),
                $this->get('oro_user.dashboard.owner_helper')->getOwnerIds($options)
            );

        // prepare chart data
        if (empty($data)) {
            $data[] = ['label' => $translator->trans('orocrm.sales.lead.source.none')];
        } else {
            // translate sources
            foreach ($data as &$item) {
                if ($item['source'] === null) {
                    $item['label'] = $translator->trans('orocrm.sales.lead.source.unclassified');
                } elseif (empty($item['source'])) {
                    $item['label'] = $translator->trans('orocrm.sales.lead.source.others');
                } else {
                    $item['label'] = $enumValueTranslator->transEnum($item['source'], 'lead_source');
                }
                unset($item['source']);
            }
            // sort alphabetically by label
            usort(
                $data,
                function ($a, $b) {
                    return strcasecmp($a['label'], $b['label']);
                }
            );
        }

        $widgetAttr              = $this->get('oro_dashboard.widget_configs')->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get('oro_chart.view_builder')
            ->setArrayData($data)
            ->setOptions(
                [
                    'name'        => 'pie_chart',
                    'data_schema' => [
                        'label' => ['field_name' => 'label'],
                        'value' => ['field_name' => 'itemCount']
                    ]
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
        $workflows       = $workflowManager->getApplicableWorkflows(SalesFunnel::class);

        $customStepCalculations = ['won_opportunity' => 'opportunity.closeRevenue'];

        /** @var SalesFunnelRepository $salesFunnerRepository */
        $salesFunnerRepository = $this->getDoctrine()->getRepository('OroCRMSalesBundle:SalesFunnel');

        $data = $salesFunnerRepository->getFunnelChartData(
            $dateFrom,
            $dateTo,
            $workflows ? reset($workflows) : null,
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
