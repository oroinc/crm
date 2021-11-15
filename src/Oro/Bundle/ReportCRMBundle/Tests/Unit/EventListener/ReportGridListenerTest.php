<?php

namespace Oro\Bundle\ReportCRMBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Provider\State\DatagridStateProviderInterface;
use Oro\Bundle\ReportCRMBundle\EventListener\ReportGridListener;

class ReportGridListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider onBuildBeforeDataProvider
     */
    public function testOnBuildBefore(
        array $datagridConfigArray,
        array $filtersState,
        array $expectedDatagridConfigArray
    ): void {
        $datagridConfig = DatagridConfiguration::create($datagridConfigArray);
        $parameters = new ParameterBag();
        $datagrid = new Datagrid('sample-grid', $datagridConfig, $parameters);
        $event = new BuildBefore($datagrid, $datagridConfig);
        $filtersStateProvider = $this->createMock(DatagridStateProviderInterface::class);

        $filtersStateProvider->expects($this->once())
            ->method('getState')
            ->with($datagridConfig, $parameters)
            ->willReturn($filtersState);

        $listener = new ReportGridListener($filtersStateProvider);
        $listener->onBuildBefore($event);
        self::assertEquals($expectedDatagridConfigArray, $event->getDatagrid()->getConfig()->toArray());
    }

    public function onBuildBeforeDataProvider(): array
    {
        return [
            'period is set' => [
                'datagridConfigArray' => [],
                'filtersState' => [
                    ReportGridListener::PERIOD_COLUMN_NAME => ['value' => 'quarterPeriod'],
                ],
                'expectedDatagridConfigArray' => [
                    'filters' => ['columns' => ['period' => ['data_name' => 'quarterPeriod']]],
                    'columns' => ['period' => ['data_name' => 'quarterPeriod']],
                    'source' => ['query' => ['select' => [], 'groupBy' => 'quarterPeriod']],
                    'sorters' => ['columns' => ['period' => ['data_name' => 'quarterPeriodSorting']]],
                ],
            ],
            'period is monthPeriod when not set' => [
                'datagridConfigArray' => [],
                'filtersState' => [],
                'expectedDatagridConfigArray' => [
                    'filters' => ['columns' => ['period' => ['data_name' => 'monthPeriod']]],
                    'columns' => ['period' => ['data_name' => 'monthPeriod']],
                    'source' => ['query' => ['select' => [], 'groupBy' => 'monthPeriod']],
                    'sorters' => ['columns' => ['period' => ['data_name' => 'monthPeriodSorting']]],
                ],
            ],
            'alias fields are excluded from select' => [
                'datagridConfigArray' => [
                    'source' => [
                        'query' => [
                            'select' => [
                                'monthPeriod',
                                'monthPeriodSorting',
                                'quarterPeriod',
                                'quarterPeriodSorting',
                                'yearPeriod',
                            ],
                        ],
                    ],
                ],
                'filtersState' => [],
                'expectedDatagridConfigArray' => [
                    'filters' => ['columns' => ['period' => ['data_name' => 'monthPeriod']]],
                    'columns' => ['period' => ['data_name' => 'monthPeriod']],
                    'source' => [
                        'query' => [
                            'select' => ['monthPeriod', 'monthPeriodSorting'],
                            'groupBy' => 'monthPeriod',
                        ]
                    ],
                    'sorters' => ['columns' => ['period' => ['data_name' => 'monthPeriodSorting']]],
                ],
            ],
            'sort alias is set to period if period is yearPeriod, group by is added when period is not empty' => [
                'datagridConfigArray' => [],
                'filtersState' => [
                    ReportGridListener::PERIOD_COLUMN_NAME => ['value' => 'yearPeriod'],
                ],
                'expectedDatagridConfigArray' => [
                    'filters' => ['columns' => ['period' => ['data_name' => 'yearPeriod']]],
                    'columns' => ['period' => ['data_name' => 'yearPeriod']],
                    'source' => ['query' => ['select' => [], 'groupBy' => 'yearPeriod']],
                    'sorters' => ['columns' => ['period' => ['data_name' => 'yearPeriod']]],
                ],
            ],
            'sort alias is set to %period%Sorting if period is not yearPeriod' => [
                'datagridConfigArray' => [],
                'filtersState' => [
                    ReportGridListener::PERIOD_COLUMN_NAME => ['value' => 'quarterPeriod'],
                ],
                'expectedDatagridConfigArray' => [
                    'filters' => ['columns' => ['period' => ['data_name' => 'quarterPeriod']]],
                    'columns' => ['period' => ['data_name' => 'quarterPeriod']],
                    'source' => ['query' => ['select' => [], 'groupBy' => 'quarterPeriod']],
                    'sorters' => ['columns' => ['period' => ['data_name' => 'quarterPeriodSorting']]],
                ],
            ],
        ];
    }
}
