<?php

namespace Oro\Bundle\SalesBundle\Controller\Dashboard;

use Oro\Bundle\ChartBundle\Model\ChartViewBuilder;
use Oro\Bundle\DashboardBundle\Model\WidgetConfigs;
use Oro\Bundle\SalesBundle\Dashboard\Provider\OpportunityByStatusProvider;
use Oro\Bundle\SalesBundle\Dashboard\Provider\WidgetOpportunityByLeadSourceProvider;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\UserBundle\Dashboard\OwnerHelper;
use Oro\Bundle\WorkflowBundle\Model\WorkflowRegistry;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles dashboard actions logic.
 */
class DashboardController extends AbstractController
{
    #[\Override]
    public static function getSubscribedServices(): array
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

    #[Route(
        path: '/opportunities_by_lead_source/chart/{widget}',
        name: 'oro_sales_dashboard_opportunities_by_lead_source_chart',
        requirements: ['widget' => '[\w\-]+']
    )]
    #[Template('@OroSales/Dashboard/opportunitiesByLeadSource.html.twig')]
    public function opportunitiesByLeadSourceAction(Request $request, mixed $widget): array
    {
        $options = $this->container->get(WidgetConfigs::class)->getWidgetOptions(
            $request->query->get('_widgetId', null)
        );

        $byAmount = (bool) $options->get('byAmount', false);

        $data = $this->container->get(WidgetOpportunityByLeadSourceProvider::class)->getChartData(
            $options->get('dateRange', []),
            $this->container->get(OwnerHelper::class)->getOwnerIds($options),
            (array) $options->get('excludedSources'),
            $byAmount
        );

        $widgetAttr = $this->container->get(WidgetConfigs::class)->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->container->get(ChartViewBuilder::class)
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

    #[Route(
        path: '/opportunity_state/chart/{widget}',
        name: 'oro_sales_dashboard_opportunity_by_state_chart',
        requirements: ['widget' => '[\w\-]+']
    )]
    #[Template('@OroSales/Dashboard/opportunityByStatus.html.twig')]
    public function opportunityByStatusAction(Request $request, mixed $widget): array
    {
        $options = $this->container->get(WidgetConfigs::class)->getWidgetOptions($request->query->get('_widgetId'));
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
        $items = $this->container->get(OpportunityByStatusProvider::class)->getOpportunitiesGroupedByStatus($options);
        $widgetAttr = $this->container->get(WidgetConfigs::class)->getWidgetAttributesForTwig($widget);
        $widgetAttr['chartView'] = $this->container->get(ChartViewBuilder::class)
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
