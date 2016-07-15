<?php

namespace OroCRM\Bundle\SalesBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Entity\GridView;
use Oro\Bundle\DataGridBundle\Extension\GridViews\AbstractViewsList;

class LeadViewList extends AbstractViewsList
{
    protected $systemViews =  [
        [
            'name'         => 'lead.open',
            'label'         => 'orocrm.sales.lead.datagrid.views.open',
            'is_default'    => true,
            'grid_name'     => 'sales-lead-grid',
            'type'          => GridView::TYPE_PUBLIC,
            'filters'       => [
                'statusLabel' => [
                    'value' => "new"
                ]
            ],
            'sorters'       => [],
            'columns'       => []
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
