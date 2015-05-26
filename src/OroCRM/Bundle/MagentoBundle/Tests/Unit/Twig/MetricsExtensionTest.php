<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Twig;

use OroCRM\Bundle\MagentoBundle\Twig\MetricsExtension;

class MetricsExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $metricsExtension;

    public function setUp()
    {
        $this->metricsExtension = new MetricsExtension();
    }

    /**
     * @dataProvider filterItemsProvider
     */
    public function testFilterItems($items, $config, $expectedItems)
    {
        $filteredItems = $this->metricsExtension->filterItems($items, $config);
        $this->assertEquals($expectedItems, $filteredItems);
        $this->assertEquals(array_keys($expectedItems), array_keys($filteredItems));
    }

    public function filterItemsProvider()
    {
        return [
            $this->getDataInOrder(),
            $this->getUnsortedData(),
            $this->getUnfilteredData(),
            $this->getMixedData(),
        ];
    }

    protected function getDataInOrder()
    {
        return [
            $items = [
                'revenue' => [
                    'label' => 'Revenue',
                ],
                'orders_number' => [
                    'label' => 'Orders number',
                ],
            ],
            $config = [
                'metrics' => [
                    [
                        'id'    => 'revenue',
                        'label' => 'Revenue',
                        'show'  => true,
                        'order' => 1,
                    ],
                    [
                        'id'    => 'orders_number',
                        'label' => 'Orders number',
                        'show'  => true,
                        'order' => 2,
                    ],
                ],
            ],
            $expectedItems = [
                'revenue' => [
                    'label' => 'Revenue',
                ],
                'orders_number' => [
                    'label' => 'Orders number',
                ],
            ],
        ];
    }

    protected function getUnsortedData()
    {
        return [
            $items = [
                'revenue' => [
                    'label' => 'Revenue',
                ],
                'orders_number' => [
                    'label' => 'Orders number',
                ],
            ],
            $config = [
                'metrics' => [
                    [
                        'id'    => 'revenue',
                        'label' => 'Revenue',
                        'show'  => true,
                        'order' => 2,
                    ],
                    [
                        'id'    => 'orders_number',
                        'label' => 'Orders number',
                        'show'  => true,
                        'order' => 1,
                    ],
                ],
            ],
            $expectedItems = [
                'orders_number' => [
                    'label' => 'Orders number',
                ],
                'revenue' => [
                    'label' => 'Revenue',
                ],
            ],
        ];
    }

    protected function getUnfilteredData()
    {
        return [
            $items = [
                'revenue' => [
                    'label' => 'Revenue',
                ],
                'orders_number' => [
                    'label' => 'Orders number',
                ],
            ],
            $config = [
                'metrics' => [
                    [
                        'id'    => 'revenue',
                        'label' => 'Revenue',
                        'show'  => false,
                        'order' => 1,
                    ],
                    [
                        'id'    => 'orders_number',
                        'label' => 'Orders number',
                        'show'  => true,
                        'order' => 2,
                    ],
                ],
            ],
            $expectedItems = [
                'orders_number' => [
                    'label' => 'Orders number',
                ],
            ],
        ];
    }

    protected function getMixedData()
    {
        return [
            $items = [
                'revenue' => [
                    'label' => 'Revenue',
                ],
                'orders_number' => [
                    'label' => 'Orders number',
                ],
                'another' => [
                    'label' => 'Another',
                ],
            ],
            $config = [
                'metrics' => [
                    [
                        'id'    => 'revenue',
                        'label' => 'Revenue',
                        'show'  => false,
                        'order' => 3,
                    ],
                    [
                        'id'    => 'orders_number',
                        'label' => 'Orders number',
                        'show'  => true,
                        'order' => 2,
                    ],
                    [
                        'id'    => 'another',
                        'label' => 'Another',
                        'show'  => true,
                        'order' => 1,
                    ],
                ],
            ],
            $expectedItems = [
                'another' => [
                    'label' => 'Another',
                ],
                'orders_number' => [
                    'label' => 'Orders number',
                ],
            ],
        ];
    }
}
