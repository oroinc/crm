<?php

namespace OroCRM\Bundle\SalesBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Entity\GridView;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\EnumFilterType;
use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;

class OpportunityViewList extends AbstractViewsList
{
    protected $systemViews =  [
        [
            'name'         => 'opportunity.open',
            'label'         => 'orocrm.sales.opportunity.datagrid.views.open',
            'is_default'    => true,
            'grid_name'     => 'sales-opportunity-grid',
            'type'          => GridView::TYPE_PUBLIC,
            'filters'       => [
                'status' => [
                    'type'  => EnumFilterType::TYPE_NOT_IN,
                    'value' => ['lost', 'won']
                ]
            ],
            'sorters'       => [],
            'columns'       => []
        ], [
            'name'         => 'opportunity.overdue',
            'label'         => 'orocrm.sales.opportunity.datagrid.views.overdue',
            'is_default'    => false,
            'grid_name'     => 'sales-opportunity-grid',
            'type'          => GridView::TYPE_PUBLIC,
            'filters'       => [
                'closeDate' => [
                    'type'  => AbstractDateFilterType::TYPE_MORE_THAN,
                    'value' => ['start' => '{{2}}', 'end' => '']
                ],
                'status' => [
                    'type'  => EnumFilterType::TYPE_NOT_IN,
                    'value' => ['lost', 'won']
                ]
            ],
            'sorters'       => [
                'closeDate' => 1
            ],
            'columns'       => []
    
        ], [
            'name'         => 'opportunity.recently_closed',
            'label'         => 'orocrm.sales.opportunity.datagrid.views.recently_closed',
            'is_default'    => false,
            'grid_name'     => 'sales-opportunity-grid',
            'type'          => GridView::TYPE_PUBLIC,
            'filters'       => [
                'closeDate' => [
                    'type'  => AbstractDateFilterType::TYPE_BETWEEN,
                    'value' => ['start' => '{{2}} - 30', 'end' => '{{2}}']
                ],
                'status' => [
                    'type'  => EnumFilterType::TYPE_IN,
                    'value' => ['won']
                ]
            ],
            'sorters'       => [
                'closeDate' => -1
            ],
            'columns'       => [
                'channelName'          => ['renderable' => true, 'order' => 0],
                'name'                 => ['renderable' => true, 'order' => 1],
                'createdAt'            => ['renderable' => true, 'order' => 2],
                'updatedAt'            => ['renderable' => false, 'order' => 3],
                'contactName'          => ['renderable' => true, 'order' => 4],
                'customerName'         => ['renderable' => false, 'order' => 5],
                'closeReasonLabel'     => ['renderable' => false, 'order' => 6],
                'closeDate'            => ['renderable' => true, 'order' => 7],
                'closeRevenue'         => ['renderable' => true, 'order' => 8],
                'budgetAmount'         => ['renderable' => false, 'order' => 9],
                'probability'          => ['renderable' => true, 'order' => 10],
                'status'               => ['renderable' => true, 'order' => 11],
                'primaryEmail'         => ['renderable' => true, 'order' => 12],
                'ownerName'            => ['renderable' => true, 'order' => 13],
                'timesContacted'       => ['renderable' => false, 'order' => 14],
                'timesContactedIn'     => ['renderable' => false, 'order' => 15],
                'timesContactedOut'    => ['renderable' => false, 'order' => 16],
                'lastContactedDate'    => ['renderable' => false, 'order' => 17],
                'lastContactedDateIn'  => ['renderable' => false, 'order' => 17],
                'lastContactedDateOut' => ['renderable' => false, 'order' => 18],
                'daysSinceLastContact' => ['renderable' => false, 'order' => 19],
                'tags'                 => ['renderable' => true, 'order' => 20],
            ]
        ]
    ];

    /**
     * {@inheritDoc}
     */
    protected function getViewsList()
    {
        return $this->getSystemViewsList();
    }
}
