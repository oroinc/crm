<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Model;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;
use OroCRM\Bundle\MarketingListBundle\Model\DataGridConfigurationHelper;

class DataGridConfigurationHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataGridConfigurationHelper
     */
    protected $helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ConfigurationProviderInterface
     */
    protected $configProvider;

    protected function setUp()
    {
        $this->configProvider = $this->getMock('Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface');

        $this->helper = new DataGridConfigurationHelper($this->configProvider);
    }

    /**
     * @param string $gridName
     * @param array  $existingParameters
     * @param array  $additionalParameters
     * @param array  $expectedParameters
     *
     * @dataProvider extendConfigurationDataProvider
     */
    public function testExtendConfiguration(
        $gridName,
        array $existingParameters,
        array $additionalParameters,
        array $expectedParameters
    ) {
        $this->configProvider
            ->expects($this->once())
            ->method('getConfiguration')
            ->will(
                $this->returnValue(
                    DatagridConfiguration::create($additionalParameters)
                )
            );

        $this->assertEquals(
            DatagridConfiguration::create($expectedParameters)->toArray(),
            $this->helper->extendConfiguration(DatagridConfiguration::create($existingParameters), $gridName)->toArray()
        );
    }

    /**
     * @return array
     */
    public function extendConfigurationDataProvider()
    {
        $emptyParameters = ['source' => [], 'sorters' => [], 'filters' => [], 'columns' => null];
        return [
            'empty'          => [
                'gridName'             => 'gridName',
                'existingParameters'   => [],
                'additionalParameters' => [],
                'expectedParameters'   => $emptyParameters
            ],
            'leave_name'     => [
                'gridName'             => 'gridName',
                'existingParameters'   => ['name' => 'existing'],
                'additionalParameters' => ['name' => 'additional'],
                'expectedParameters'   => array_merge($emptyParameters, ['name' => 'existing'])
            ],
            'not_array'      => [
                'gridName'             => 'gridName',
                'existingParameters'   => ['scope' => 'existing'],
                'additionalParameters' => ['scope' => 'additional'],
                'expectedParameters'   => array_merge($emptyParameters, ['scope' => 'existing'])
            ],
            'merge'          => [
                'gridName'             => 'gridName',
                'existingParameters'   => ['scope' => ['existing']],
                'additionalParameters' => ['scope' => ['additional']],
                'expectedParameters'   => array_merge($emptyParameters, ['scope' => ['existing', 'additional']])
            ],
            'add_new'        => [
                'gridName'             => 'gridName',
                'existingParameters'   => [],
                'additionalParameters' => ['scope' => ['additional']],
                'expectedParameters'   => array_merge($emptyParameters, ['scope' => ['additional']])
            ],
            'without_update' => [
                'gridName'             => 'gridName',
                'existingParameters'   => ['scope' => ['existing']],
                'additionalParameters' => [],
                'expectedParameters'   => array_merge($emptyParameters, ['scope' => ['existing']])
            ],
            'with alias update' => [
            'gridName'             => 'gridName',
            'existingParameters'   => [
                'source' => [
                    'query' => [
                        'from' => [
                            [
                                'table' => 'table',
                                'alias' => 'T1000'
                            ]
                        ]
                    ]
                ],
                'columns' => ['T1000.name as name']
            ],
            'additionalParameters' => [
                'columns' => ['__root_entity__.id', 'other.field'],
                'sorters' => [
                    'columns' => ['__root_entity__.id', 'other.field']
                ],
                'filters' => [
                    'columns' => ['__root_entity__.id', 'other.field']
                ],
                'source' => [
                    'query' => [
                        'where' => 'other = some.type AND __root_entity__.id = some.id'
                    ]
                ]
            ],
            'expectedParameters'   => array_merge(
                $emptyParameters,
                [
                    'columns' => ['T1000.name as name', 'T1000.id', 'other.field'],
                    'sorters' => [
                        'columns' => ['T1000.id', 'other.field']
                    ],
                    'filters' => [
                        'columns' => ['T1000.id', 'other.field']
                    ],
                    'source' => [
                        'query' => [
                            'from' => [
                                [
                                    'table' => 'table',
                                    'alias' => 'T1000'
                                ]
                            ],
                            'where' => 'other = some.type AND T1000.id = some.id'
                        ]
                    ]
                ]
            )
        ],
        ];
    }
}
