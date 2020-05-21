<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\MagentoBundle\Provider\BatchFilterBag;

class BatchFilterBagTest extends \PHPUnit\Framework\TestCase
{
    /** @var BatchFilterBag */
    protected $filter;

    protected function setUp(): void
    {
        $this->filter = new BatchFilterBag();
    }

    public function testFilters()
    {
        // add only last id filter
        $this->filter->addLastIdFilter(1);
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(1, $filters['complex_filter']);
        $this->assertNotEmpty($filters['complex_filter'][0]);

        // add date filter in initial mode
        $this->filter->addDateFilter('created_at', 'to', new \DateTime());
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(2, $filters['complex_filter']);
        $this->assertNotEmpty($filters['complex_filter'][1]);
        $this->assertContains('created_at', $filters['complex_filter'][1]);
        $this->assertEquals('to', $filters['complex_filter'][1]['value']['key']);

        $this->filter->addDateFilter('updated_at', 'from', new \DateTime());
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(3, $filters['complex_filter']); // still should be two filters
        $this->assertContains('updated_at', $filters['complex_filter'][2]);
        $this->assertEquals('from', $filters['complex_filter'][2]['value']['key']);

        // add website filter
        $this->filter->addWebsiteFilter([1]);
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(4, $filters['complex_filter']);
        $this->assertContains('website_id', $filters['complex_filter'][3]);

        // add store filter
        $this->filter->addStoreFilter([1]);
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(5, $filters['complex_filter']);
        $this->assertContains('store_id', $filters['complex_filter'][4]);
    }

    public function testReset()
    {
        // add only last id filter
        $this->filter->addLastIdFilter(1);
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(1, $filters['complex_filter']);

        // add date filter in initial mode
        $this->filter->addDateFilter('created_at', 'to', new \DateTime());
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(2, $filters['complex_filter']);

        // add dummy simple filter
        $this->filter->addFilter('test', ['test' => true]);
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(1, $filters['filter']);

        // reset only simple filter
        $this->filter->reset('filter');
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertArrayNotHasKey('filter', $filters);
        $this->filter->reset();
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertArrayNotHasKey('complex_filter', $filters);
        $this->assertArrayNotHasKey('filter', $filters);

        // add dummy simple filter
        $this->filter->addFilter('test', ['test' => true]);
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertCount(1, $filters['filter']);

        $this->filter->reset(BatchFilterBag::FILTER_TYPE_SIMPLE, 'test');
        $filters = $this->getAppliedFilters($this->filter);
        $this->assertFalse(isset($filters['filter']['test']));
    }

    public function testConstructAddition()
    {
        $testFilters        = ['testField1' => 'testValue1'];
        $testComplexFilters = ['testFieldComplex' => ['key' => 'in', 'value' => 'some, list, of, values']];

        $bag = new BatchFilterBag($testFilters, $testComplexFilters);

        $result = $bag->getAppliedFilters();

        $expected = [
            'filters' => [
                'filter'         => [['key' => 'testField1', 'value' => 'testValue1']],
                'complex_filter' => [
                    [
                        'key'   => 'testFieldComplex',
                        'value' => [
                            'key'   => 'in',
                            'value' => 'some, list, of, values',
                        ]
                    ]
                ]
            ]
        ];
        $this->assertSame($expected, $result);
    }

    public function testMerge()
    {
        $testFilters        = ['testField1' => 'testValue1'];
        $testComplexFilters = ['testFieldComplex' => ['key' => 'in', 'value' => 'some, list, of, values']];
        $bag                = new BatchFilterBag($testFilters, $testComplexFilters);

        $testFilters2        = ['testField1' => 'Overriden', 'testField2' => 'testValue2'];
        $testComplexFilters2 = ['testField2Complex' => ['key' => 'gt', 'value' => 3]];
        $bag2                = new BatchFilterBag($testFilters2, $testComplexFilters2);

        $bag->merge($bag2);
        $result = $bag->getAppliedFilters();

        $expected = [
            'filters' => [
                'filter'         => [
                    ['key' => 'testField1', 'value' => 'Overriden'],
                    ['key' => 'testField2', 'value' => 'testValue2']
                ],
                'complex_filter' => [
                    [
                        'key'   => 'testFieldComplex',
                        'value' => [
                            'key'   => 'in',
                            'value' => 'some, list, of, values',
                        ],
                    ],
                    [
                        'key'   => 'testField2Complex',
                        'value' => [
                            'key'   => 'gt',
                            'value' => 3,
                        ]
                    ]
                ]
            ]
        ];
        $this->assertSame($expected, $result);
    }

    /**
     * @param array $filters
     * @param array $complexFilters
     * @param array $expectedBeforeReset
     * @param array $expectedAfterReset
     *
     * @dataProvider providerResetFilterWithEmptyValue
     */
    public function testResetFilterWithEmptyValue($filters, $complexFilters, $expectedBeforeReset, $expectedAfterReset)
    {
        $bag = new BatchFilterBag($filters, $complexFilters);
        $filters = $bag->getAppliedFilters();
        self::assertEquals($expectedBeforeReset, $filters);

        $bag->resetFilterWithEmptyValue();
        $filters = $bag->getAppliedFilters();
        self::assertEquals($expectedAfterReset, $filters);
    }

    /**
     * Data provider for testResetFilterWithEmptyValue
     *
     * @return array
     */
    public function providerResetFilterWithEmptyValue()
    {
        return [
            'all filters have empty value' => [
                'filters' => [
                    'testField1' => null
                ],
                'complexFilters' => [
                    'testFieldComplex' => null
                ],
                'expectedBeforeReset' => [
                    'filters' => [
                        'filter' => [
                            ['key' => 'testField1', 'value' => null]
                        ],
                        'complex_filter' => [
                            ['key' => 'testFieldComplex', 'value' => null]
                        ],
                    ]
                ],
                'expectedAfterReset' => [
                    'filters' => []
                ]
            ],
            'one filter with empty value' => [
                'filters' => [
                    'testField1' => 'testValue1',
                    'testField2' => null,
                ],
                'complexFilters' => [
                    'testFieldComplex' => 'testValue1'
                ],
                'expectedBeforeReset' => [
                    'filters' => [
                        'filter' => [
                            ['key' => 'testField1', 'value' => 'testValue1'],
                            ['key' => 'testField2', 'value' => null]
                        ],
                        'complex_filter' => [
                            ['key' => 'testFieldComplex', 'value' => 'testValue1']
                        ],
                    ]
                ],
                'expectedAfterReset' => [
                    'filters' => [
                        'filter' => [
                            ['key' => 'testField1', 'value' => 'testValue1']
                        ],
                        'complex_filter' => [
                            ['key' => 'testFieldComplex', 'value' => 'testValue1']
                        ],
                    ]
                ]
            ],
            'one complex_filter with empty value' => [
                'filters' => [
                    'testField1' => 'testValue1',
                    'testField2' => 'testValue2'
                ],
                'complexFilters' => [
                    'testFieldComplex' => 'testValue1',
                    'testFieldComplex2' => null
                ],
                'expectedBeforeReset' => [
                    'filters' => [
                        'filter' => [
                            ['key' => 'testField1', 'value' => 'testValue1'],
                            ['key' => 'testField2', 'value' => 'testValue2']
                        ],
                        'complex_filter' => [
                            ['key' => 'testFieldComplex', 'value' => 'testValue1'],
                            ['key' => 'testFieldComplex2', 'value' => null]
                        ]
                    ],
                ],
                'expectedAfterReset' => [
                    'filters' => [
                        'filter' => [
                            ['key' => 'testField1', 'value' => 'testValue1'],
                            ['key' => 'testField2', 'value' => 'testValue2']
                        ],
                        'complex_filter' => [
                            ['key' => 'testFieldComplex', 'value' => 'testValue1']
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @param BatchFilterBag $bag
     *
     * @return array
     */
    protected function getAppliedFilters(BatchFilterBag $bag)
    {
        $filters = $bag->getAppliedFilters();
        $this->assertArrayHasKey('filters', $filters);
        $filters = $filters['filters'];

        return $filters;
    }
}
