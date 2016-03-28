<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use OroCRM\Bundle\MagentoBundle\EventListener\OrderGridListener;

class OrderGridListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrderGridListener|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $listener;

    /**
     * @dataProvider onBuildBeforeDataProvider
     *
     * @param array $params
     * @param array $config
     * @param array $expected
     */
    public function testOnBuildBefore(array $params, array $config, array $expected)
    {
        $gridConfig     = DatagridConfiguration::create($config);
        $parameters     = new ParameterBag($params);
        $datagrid       = new Datagrid('magento-order-grid', $gridConfig, $parameters);
        $event          = new BuildBefore($datagrid, $gridConfig);
        $datagridHelper = $this->getMockBuilder('Oro\Bundle\AddressBundle\Datagrid\CountryDatagridHelper')
            ->setMethods(['getCountryFilterQueryBuilder'])
            ->disableOriginalConstructor()
            ->getMock();

        $listener = new OrderGridListener($datagridHelper);
        $listener->onBuildBefore($event);
        $this->assertEquals($expected, $event->getDatagrid()->getConfig()->toArray());
    }

    public function onBuildBeforeDataProvider()
    {
        return [
            'filters active'    => [
                ['_minified' => ['f' => ['countryName' => 'US']]],
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
                                        'property'             => 'name',
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
                                            'FROM OroCRMMagentoBundle:OrderAddress oa LEFT JOIN '.
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
                                        'property'             => 'name',
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
