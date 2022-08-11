<?php

namespace Oro\Bundle\SalesBundle\Controller\Dashboard;

use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\DashboardBundle\Model\WidgetConfigs;
use Oro\Bundle\SalesBundle\Dashboard\Provider\OpportunityByStatusProvider;
use Oro\Bundle\SalesBundle\Dashboard\Provider\WidgetOpportunityByLeadSourceProvider;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\UserBundle\Dashboard\OwnerHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles dashboard actions logic.
 */
class DashboardController extends AbstractController
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            WidgetConfigs::class,
            WidgetOpportunityByLeadSourceProvider::class,
            OwnerHelper::class,
            ChartViewBuilder::class,
            OpportunityByStatusProvider::class,
            WorkflowRegistry::class,
            AclHelper::class
        ]);
    }

    /**
     * @Route(
     *      "/opportunities_by_lead_source/chart/{widget}",
     *      name="oro_sales_dashboard_opportunities_by_lead_source_chart",
     *      requirements={"widget"="[\w\-]+"}
     * )
     * @Template("@OroSales/Dashboard/opportunitiesByLeadSource.html.twig")
     */
    public function opportunitiesByLeadSourceAction(Request $request, mixed $widget): array
    {
        $options = $this->get(WidgetConfigs::class)->getWidgetOptions(
            $request->query->get('_widgetId', null)
        );

        $byAmount = (bool) $options->get('byAmount', false);

        $data = $this->get(WidgetOpportunityByLeadSourceProvider::class)->getChartData(
            $options->get('dateRange', []),
            $this->get(OwnerHelper::class)->getOwnerIds($options),
            (array) $options->get('excludedSources'),
            $byAmount
        );

        $widgetAttr = $this->get(WidgetConfigs::class)->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get(ChartViewBuilder::class)
            ->setArrayData($data)
            ->setOptions(
                [
                    'name' => 'pie_chart',
                    'data_schema' => [
                        'label' => ['field_name' => 'source'],
                        'value' => ['field_name' => 'value'],
                    ],
                    'settings' => [
                        'showPercentValues' => 1,
                        'showPercentInTooltip' => 0,
                        'valuePrefix' => $byAmount ? '$' : '',
                    ]
                ]
            )
            ->getView();

        return $widgetAttr;
    }

    /**
     * @Route(
     *      "/opportunity_state/chart/{widget}",
     *      name="oro_sales_dashboard_opportunity_by_state_chart",
     *      requirements={"widget"="[\w\-]+"}
     * )
     * @Template("@OroSales/Dashboard/opportunityByStatus.html.twig")
     */
    public function opportunityByStatusAction(Request $request, mixed $widget): array
    {
        $options = $this->get(WidgetConfigs::class)->getWidgetOptions($request->query->get('_widgetId', null));
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
        $items = $this->get(OpportunityByStatusProvider::class)->getOpportunitiesGroupedByStatus($options);
        $widgetAttr = $this->get(WidgetConfigs::class)->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->get(ChartViewBuilder::class)
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
}
