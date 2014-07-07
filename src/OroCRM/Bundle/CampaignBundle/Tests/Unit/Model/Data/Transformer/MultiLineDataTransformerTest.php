<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Model\Data\Transformer;

use Oro\Bundle\ChartBundle\Model\Data\ArrayData;
use Oro\Bundle\ChartBundle\Model\Data\MappedData;
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

        $data = new MappedData($data, $sourceData);

        $result = $this->transformer->transform($data, $chartOptions);

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
            'fill_labels' => [
                [
                    [
                        'option' => 'o1',
                        'label'  => '2014-07-07',
                        'value'  => 'v1',
                    ],
                    [
                        'option' => 'o2',
                        'label'  => '2014-07-09',
                        'value'  => 'v2',
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
                        'period'         => 'daily'
                    ]
                ],
                [
                    'o1' => [
                        [
                            'label' => '2014-07-07',
                            'value' => 'v1'
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
                            'label' => '2014-07-09',
                            'value' => 'v2'
                        ],
                        [
                            'label' => '2014-07-07',
                            'value' => 0
                        ],
                        [
                            'label' => '2014-07-08',
                            'value' => 0
                        ]
                    ],
                ]
            ],
            'skip_labels' => [
                [
                    [
                        'option' => 'o1',
                        'label'  => '2014-07-07',
                        'value'  => 'v1',
                    ],
                    [
                        'option' => 'o2',
                        'label'  => '2014-07-09',
                        'value'  => 'v2',
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
                        'groupingOption' => 'option'
                    ]
                ],
                [
                    'o1' => [
                        [
                            'label' => '2014-07-07',
                            'value' => 'v1'
                        ],
                        [
                            'label' => '2014-07-09',
                            'value' => 0
                        ]
                    ],
                    'o2' => [
                        [
                            'label' => '2014-07-09',
                            'value' => 'v2'
                        ],
                        [
                            'label' => '2014-07-07',
                            'value' => 0
                        ]
                    ],
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
        $data = new MappedData([], $sourceData);
        $this->transformer->transform($data, []);
    }
}
