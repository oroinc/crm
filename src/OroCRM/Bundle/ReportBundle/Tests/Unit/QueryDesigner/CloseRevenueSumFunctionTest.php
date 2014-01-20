<?php

namespace OroCRM\Bundle\ReportBundle\Tests\Unit\QueryDesigner;

use Oro\Bundle\QueryDesignerBundle\Tests\Unit\OrmQueryConverterTest;
use Oro\Bundle\QueryDesignerBundle\Tests\Unit\Fixtures\QueryDesignerModel;
use Oro\Bundle\QueryDesignerBundle\Grid\DatagridConfigurationQueryConverter;

class CloseRevenueSumFunctionTest extends OrmQueryConverterTest
{
    public function testFunction()
    {
        $gridName         = 'test_grid';
        $definition       = [
            'columns'          => [
                ['name' => 'id', 'label' => 'id'],
                [
                    'name'  => 'opportunity+OroCRM\Bundle\SalesBundle\Entity\Opportunity::closeRevenue',
                    'label' => 'SUM of closeRevenue',
                    'func'  => [
                        'name'       => 'ClosedCloseRevenueSum',
                        'group_name' => 'opportunity_by_workflow_item_close_revenue',
                        'group_type' => 'aggregates'
                    ]
                ],
            ],
            'grouping_columns' => [],
            'filters'          => [],
            'filters_logic'    => ''
        ];
        $doctrine         = $this->getDoctrine(
            [
                'OroCRM\Bundle\ReportBundle\Entity\OpportunityByWorkflowItem' => [
                    'id'           => 'integer',
                    'opportunity'  => ['nullable' => true],
                    'workflowItem' => ['nullable' => true],
                ],
                'OroCRM\Bundle\SalesBundle\Entity\Opportunity'                => ['closeRevenue' => 'float'],
            ]
        );
        $functionProvider = $this->getFunctionProvider(
            [
                [
                    'ClosedCloseRevenueSum',
                    'opportunity_by_workflow_item_close_revenue',
                    'aggregates',
                    [
                        'name' => 'ClosedCloseRevenueSum',
                        'expr' => '@OroCRM\Bundle\ReportBundle\QueryDesigner\CloseRevenueSumFunction'
                    ]
                ]
            ]
        );

        $model = new QueryDesignerModel();
        $model->setEntity('OroCRM\Bundle\ReportBundle\Entity\OpportunityByWorkflowItem');
        $model->setDefinition(json_encode($definition));
        $converter = new DatagridConfigurationQueryConverter($functionProvider, $doctrine);
        $result    = $converter->convert($gridName, $model)->toArray()['source'];

        $expected = [
            'query_config' =>
                [
                    'table_aliases'  => [
                        ''                                                                          => 't1',
                        'OroCRM\Bundle\ReportBundle\Entity\OpportunityByWorkflowItem::opportunity'  => 't2',
                        'OroCRM\Bundle\ReportBundle\Entity\OpportunityByWorkflowItem::workflowItem' => 't3',
                    ],
                    'column_aliases' => [
                        'id'                                                                              => 'c1',
                        'opportunity+OroCRM\Bundle\SalesBundle\Entity\Opportunity::closeRevenue'
                        . '(ClosedCloseRevenueSum,opportunity_by_workflow_item_close_revenue,aggregates)' => 'c2'
                    ],
                ],
            'query'        => [
                'select' => [
                    't1.id as c1',
                    'SUM(CASE WHEN (t3.currentStepName=\'close\') THEN t2.closeRevenue ELSE 0 END) as c2',
                ],
                'from'   => [
                    ['table' => 'OroCRM\Bundle\ReportBundle\Entity\OpportunityByWorkflowItem', 'alias' => 't1']
                ],
                'join'   => [
                    'left' => [
                        ['join' => 't1.opportunity', 'alias' => 't2'],
                        ['join' => 't1.workflowItem', 'alias' => 't3'],
                    ]
                ]
            ],
            'type'         => 'orm'
        ];

        $this->assertEquals($expected, $result);
    }
}
