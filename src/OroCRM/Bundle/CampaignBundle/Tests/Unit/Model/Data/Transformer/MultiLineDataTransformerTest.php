<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Model\Data\Transformer;

use Oro\Bundle\ChartBundle\Model\Data\ArrayData;
use Oro\Bundle\ChartBundle\Model\Data\MappedData;
use OroCRM\Bundle\CampaignBundle\Entity\Campaign;
use OroCRM\Bundle\CampaignBundle\Model\Data\Transformer\MultiLineDataTransformer;

class MultiLineDataTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MultiLineDataTransformer
     */
    protected $transformer;

    protected function setUp()
    {
        $this->transformer = new MultiLineDataTransformer();
    }

    /**
     * @param array $data
     * @param array $chartOptions
     * @param array $expected
     *
     * @dataProvider dataProvider
     */
    public function testTransform(array $data, array $chartOptions, array $expected)
    {
        $sourceData = new ArrayData($data);

        $mapping = [
            'label' => 'label',
            'value' => 'value',
        ];

        $result = $this->transformer->transform(
            new MappedData($mapping, $sourceData),
            $chartOptions
        );


        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function dataProvider()
    {
        return [
            'one_label'   => [
                [
                    [
                        'option' => 'o1',
                        'label'  => '2014-07-09',
                        'value'  => 1,
                    ],
                    [
                        'option' => 'o2',
                        'label'  => '2014-07-09',
                        'value'  => 1,
                    ]
                ],
                [
                    'data_schema'      => [
                        'label' => [
                            'field_name' => 'label'
                        ],
                        'value' => [
                            'field_name' => 'value'
                        ]
                    ],
                    'default_settings' => [
                        'groupingOption' => 'option',
                        'period'         => Campaign::PERIOD_DAILY
                    ]
                ],
                [
                    'o1' => [
                        [
                            'label' => '2014-07-08',
                            'value' => 0
                        ],
                        [
                            'label' => '2014-07-09',
                            'value' => 1
                        ]
                    ],
                    'o2' => [
                        [
                            'label' => '2014-07-08',
                            'value' => 0
                        ],
                        [
                            'label' => '2014-07-09',
                            'value' => 1
                        ]

                    ],
                ]
            ],
            'fill_labels' => [
                [
                    [
                        'option' => 'o1',
                        'label'  => '2014-07-07',
                        'value'  => 1,
                    ],
                    [
                        'option' => 'o2',
                        'label'  => '2014-07-09',
                        'value'  => 1,
                    ]
                ],
                [
                    'data_schema'      => [
                        'label' => [
                            'field_name' => 'label'
                        ],
                        'value' => [
                            'field_name' => 'value'
                        ]
                    ],
                    'default_settings' => [
                        'groupingOption' => 'option',
                        'period'         => Campaign::PERIOD_DAILY
                    ]
                ],
                [
                    'o1' => [
                        [
                            'label' => '2014-07-06',
                            'value' => 0
                        ],
                        [
                            'label' => '2014-07-07',
                            'value' => 1
                        ],
                        [
                            'label' => '2014-07-08',
                            'value' => 0
                        ],
                        [
                            'label' => '2014-07-09',
                            'value' => 0
                        ]
                    ],
                    'o2' => [
                        [
                            'label' => '2014-07-06',
                            'value' => 0
                        ],
                        [
                            'label' => '2014-07-07',
                            'value' => 0
                        ],
                        [
                            'label' => '2014-07-08',
                            'value' => 0
                        ],
                        [
                            'label' => '2014-07-09',
                            'value' => 1
                        ],
                    ],
                ]
            ],
            'skip_labels' => [
                [
                    [
                        'option' => 'o1',
                        'label'  => '2014-07-07',
                        'value'  => 1,
                    ],
                    [
                        'option' => 'o2',
                        'label'  => '2014-07-09',
                        'value'  => 1,
                    ]
                ],
                [
                    'data_schema'      => [
                        'label' => [
                            'field_name' => 'label'
                        ],
                        'value' => [
                            'field_name' => 'value'
                        ]
                    ],
                    'default_settings' => [
                        'groupingOption' => 'option',
                        'period'         => Campaign::PERIOD_HOURLY
                    ]
                ],
                [
                    'o1' => [
                        [
                            'label' => '2014-07-07',
                            'value' => 1
                        ],
                        [
                            'label' => '2014-07-09',
                            'value' => 0
                        ]
                    ],
                    'o2' => [
                        [
                            'label' => '2014-07-07',
                            'value' => 0
                        ],
                        [
                            'label' => '2014-07-09',
                            'value' => 1
                        ]
                    ],
                ]
            ],
            'force_daily' => [
                [
                    [
                        'option' => 'o1',
                        'label'  => '2014-07-15 00:00:00.000000',
                        'value'  => 1,
                    ],
                    [
                        'option' => 'o1',
                        'label'  => '2014-08-01 00:00:00.000000',
                        'value'  => 3,
                    ]
                ],
                [
                    'data_schema'      => [
                        'label' => [
                            'field_name' => 'label'
                        ],
                        'value' => [
                            'field_name' => 'value'
                        ]
                    ],
                    'default_settings' => [
                        'groupingOption' => 'option',
                        'period'         => Campaign::PERIOD_HOURLY
                    ]
                ],
                [
                    'o1' => [
                        ['label' => '2014-07-14', 'value' => 0],
                        ['label' => '2014-07-15', 'value' => 1],
                        ['label' => '2014-07-16', 'value' => 0],
                        ['label' => '2014-07-17', 'value' => 0],
                        ['label' => '2014-07-18', 'value' => 0],
                        ['label' => '2014-07-19', 'value' => 0],
                        ['label' => '2014-07-20', 'value' => 0],
                        ['label' => '2014-07-21', 'value' => 0],
                        ['label' => '2014-07-22', 'value' => 0],
                        ['label' => '2014-07-23', 'value' => 0],
                        ['label' => '2014-07-24', 'value' => 0],
                        ['label' => '2014-07-25', 'value' => 0],
                        ['label' => '2014-07-26', 'value' => 0],
                        ['label' => '2014-07-27', 'value' => 0],
                        ['label' => '2014-07-28', 'value' => 0],
                        ['label' => '2014-07-29', 'value' => 0],
                        ['label' => '2014-07-30', 'value' => 0],
                        ['label' => '2014-07-31', 'value' => 0],
                        ['label' => '2014-08-01', 'value' => 3],
                    ]
                ]
            ]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Options "groupingOption" is not set
     */
    public function testGroupingOptionNotSet()
    {
        $sourceData = new ArrayData([]);
        $data       = new MappedData([], $sourceData);
        $this->transformer->transform($data, []);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Options "period" is not set
     */
    public function testPeriodOptionNotSet()
    {
        $sourceData = new ArrayData([]);
        $data       = new MappedData([], $sourceData);


        $chartOptions = [
            'data_schema'      => [
                'label' => [
                    'field_name' => 'label'
                ],
                'value' => [
                    'field_name' => 'value'
                ]
            ],
            'default_settings' => [
                'groupingOption' => 'option'
            ]
        ];

        $this->transformer->transform($data, $chartOptions);
    }

    public function testEmptyData()
    {
        $sourceData   = new ArrayData([]);
        $data         = new MappedData([], $sourceData);
        $chartOptions = [
            'data_schema'      => [
                'label' => [
                    'field_name' => 'label'
                ],
                'value' => [
                    'field_name' => 'value'
                ]
            ],
            'default_settings' => [
                'groupingOption' => 'option',
                'period'         => Campaign::PERIOD_DAILY
            ]
        ];

        $result = $this->transformer->transform($data, $chartOptions);
        $this->assertEquals($sourceData, $result);
    }
}
