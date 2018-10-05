<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Oro\Bundle\AddressBundle\Datagrid\CountryDatagridHelper;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Provider\State\DatagridStateProviderInterface;
use Oro\Bundle\MagentoBundle\EventListener\OrderGridListener;

class OrderGridListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OrderGridListener|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $listener;

    /**
     * @dataProvider onBuildBeforeDataProvider
     *
     * @param array $filtersState
     * @param array $config
     * @param array $expected
     */
    public function testOnBuildBefore(array $filtersState, array $config, array $expected)
    {
        $gridConfig     = DatagridConfiguration::create($config);
        $parameters     = new ParameterBag();
        $datagrid       = new Datagrid('magento-order-grid', $gridConfig, $parameters);
        $event          = new BuildBefore($datagrid, $gridConfig);
        $datagridHelper = $this->createMock(CountryDatagridHelper::class);
        $filtersStateProvider = $this->createMock(DatagridStateProviderInterface::class);

        $filtersStateProvider
            ->expects($this->once())
            ->method('getState')
            ->with($gridConfig, $parameters)
            ->willReturn($filtersState);

        $listener = new OrderGridListener($datagridHelper, $filtersStateProvider);
        $listener->onBuildBefore($event);
        $this->assertEquals($expected, $event->getDatagrid()->getConfig()->toArray());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function onBuildBeforeDataProvider()
    {
        return [
            'filters active'    => [
                ['countryName' => 'US'],
                [
                    'columns' => [
                        'test1'       => ['test1'],
                        'test2'       => ['test2'],
                        'test3'       => ['test3'],
                        'test4'       => ['test4'],
                        'test5'       => ['test5'],
                        'countryName' => ['countryName'],
                        'regionName'  => ['regionName'],
                        'test6'       => ['test6']
                    ],
                    'filters' => [
                        'columns' => [
                            'test1' => ['test1'],
                            'test2' => ['test2'],
                            'test3' => ['test3'],
                            'test4' => ['test4'],
                            'test5' => ['test5'],
                            'test6' => ['test6']
                        ],
                    ],
                    'source'  => [
                        'query' => [
                            'select' => [
                                0 => 'test1',
                                1 => 'test2',
                                2 => 'test3',
                                3 => 'test4',
                                4 => '(SELECT ...) as countryName',
                                5 => '(SELECT ...) as regionName',
                                6 => 'test5'
                            ],
                        ]
                    ]
                ],
                [
                    'columns' => [
                        'test1'       => ['test1'],
                        'test2'       => ['test2'],
                        'test3'       => ['test3'],
                        'test4'       => ['test4'],
                        'test5'       => ['test5'],
                        'countryName' => ['countryName'],
                        'regionName'  => ['regionName'],
                        'test6'       => ['test6']
                    ],
                    'filters' => [
                        'columns' => [
                            'test1'       => ['test1'],
                            'test2'       => ['test2'],
                            'test3'       => ['test3'],
                            'test4'       => ['test4'],
                            'test5'       => ['test5'],
                            'countryName' => [
                                'type'      => 'entity',
                                'data_name' => 'address.country',
                                'enabled'   => false,
                                'options'   => [
                                    'field_options' => [
                                        'class'                => 'OroAddressBundle:Country',
                                        'choice_label'         => 'name',
                                        'query_builder'        => null,
                                        'translatable_options' => false
                                    ]
                                ]
                            ],
                            'regionName'  => [
                                'type'      => 'string',
                                'data_name' => 'regionName',
                                'enabled'   => false
                            ],
                            'test6'       => ['test6'],
                        ],
                    ],
                    'source'  => [
                        'query' => [
                            'select' => [
                                0 => 'test1',
                                1 => 'test2',
                                2 => 'test3',
                                3 => 'test4',
                                4 => 'country.name as countryName',
                                5 => 'CONCAT(CASE WHEN address.regionText IS NOT NULL '.
                                    'THEN address.regionText ELSE region.name END, \'\') as regionName',
                                6 => 'test5'
                            ],
                            'join'   => [
                                'left' => [
                                    [
                                        'join'          => 'o.addresses',
                                        'alias'         => 'address',
                                        'conditionType' => 'WITH',
                                        'condition'     => 'address.id IN (SELECT oa.id '.
                                            'FROM OroMagentoBundle:OrderAddress oa LEFT JOIN '.
                                            'oa.types type WHERE type.name = \'billing\' OR type.name IS NULL)'
                                    ],
                                    [
                                        'join'  => 'address.country',
                                        'alias' => 'country',
                                    ],
                                    [
                                        'join'  => 'address.region',
                                        'alias' => 'region',
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'filter not active' => [
                [],
                [
                    'columns' => [
                        'test1'       => ['test1'],
                        'test2'       => ['test2'],
                        'test3'       => ['test3'],
                        'test4'       => ['test4'],
                        'test5'       => ['test5'],
                        'countryName' => ['countryName'],
                        'regionName'  => ['regionName'],
                        'test6'       => ['test6']
                    ],
                    'filters' => [
                        'columns' => [
                            'test1' => ['test1'],
                            'test2' => ['test2'],
                            'test3' => ['test3'],
                            'test4' => ['test4'],
                            'test5' => ['test5'],
                            'test6' => ['test6']
                        ],
                    ],
                    'source'  => [
                        'query' => [
                            'select' => [
                                0 => 'test1',
                                1 => 'test2',
                                2 => 'test3',
                                3 => 'test4',
                                4 => '(SELECT ...) as countryName',
                                5 => '(SELECT ...) as regionName',
                                6 => 'test5'
                            ],
                        ]
                    ]
                ],
                [
                    'columns' => [
                        'test1'       => ['test1'],
                        'test2'       => ['test2'],
                        'test3'       => ['test3'],
                        'test4'       => ['test4'],
                        'test5'       => ['test5'],
                        'countryName' => ['countryName'],
                        'regionName'  => ['regionName'],
                        'test6'       => ['test6']
                    ],
                    'filters' => [
                        'columns' => [
                            'test1'       => ['test1'],
                            'test2'       => ['test2'],
                            'test3'       => ['test3'],
                            'test4'       => ['test4'],
                            'test5'       => ['test5'],
                            'countryName' => [
                                'type'      => 'entity',
                                'data_name' => 'address.country',
                                'enabled'   => false,
                                'options'   => [
                                    'field_options' => [
                                        'class'                => 'OroAddressBundle:Country',
                                        'choice_label'         => 'name',
                                        'query_builder'        => null,
                                        'translatable_options' => false
                                    ]
                                ]
                            ],
                            'regionName'  => [
                                'type'      => 'string',
                                'data_name' => 'regionName',
                                'enabled'   => false
                            ],
                            'test6'       => ['test6']
                        ],
                    ],
                    'source'  => [
                        'query' => [
                            'select' => [
                                0 => 'test1',
                                1 => 'test2',
                                2 => 'test3',
                                3 => 'test4',
                                4 => '(SELECT ...) as countryName',
                                5 => '(SELECT ...) as regionName',
                                6 => 'test5'
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }
}
