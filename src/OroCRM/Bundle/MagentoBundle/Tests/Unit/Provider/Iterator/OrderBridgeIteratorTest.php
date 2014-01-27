<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\OrderBridgeIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;

class OrderBridgeIteratorTest extends BaseIteratorTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->iterator = new OrderBridgeIterator($this->transport, $this->settings);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $orderArray
     * @param string $storeData
     * @param string $stores
     * @param string $websites
     */
    public function testIteration($orderArray, $storeData, $stores, $websites)
    {
        $this->transport->expects($this->at(0))->method('getStores')
            ->will(
                $this->returnValue(new \ArrayIterator($stores))
            );

        $this->transport->expects($this->at(1))->method('getWebsites')
            ->will(
                $this->returnValue(new \ArrayIterator($websites))
            );

        $this->transport->expects($this->at(2))->method('call')
            ->with($this->equalTo('oroOrderList'))
            ->will($this->returnValue($orderArray));

        $this->transport->expects($this->at(3))->method('call')
            ->with($this->equalTo('oroOrderList'))
            ->will($this->returnValue([]));

        $orders = [
            array_merge((array)$orderArray[0], $storeData, ['items' => []]),
            array_merge((array)$orderArray[1], $storeData, ['items' => []]),
            array_merge((array)$orderArray[2], $storeData, ['items' => []]),
        ];

        $this->assertEquals(
            [
                1 => $orders[0],
                2 => $orders[1],
                3 => $orders[2],
            ],
            iterator_to_array($this->iterator)
        );
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $orderArray
     * @param string $storeData
     * @param string $stores
     * @param string $websites
     */
    public function testUpdateMode($orderArray, $storeData, $stores, $websites)
    {
        $this->iterator->setMode(UpdatedLoaderInterface::IMPORT_MODE_UPDATE);
        $this->testIteration($orderArray, $storeData, $stores, $websites);
    }

    public function dataProvider()
    {
        return [
            'one test case' => [
                [
                    (object)['order_id'   => 1,
                             'total'      => 12.5,
                             'store_id'   => 0,
                             'store_name' => 'admin',
                             'items'      => (object)[]
                    ],
                    (object)['order_id'   => 2,
                             'total'      => 132,
                             'store_id'   => 0,
                             'store_name' => 'admin',
                             'items'      => (object)[]
                    ],
                    (object)['order_id'   => 3,
                             'total'      => 86,
                             'store_id'   => 0,
                             'store_name' => 'admin',
                             'items'      => (object)[]
                    ]
                ],
                [
                    'store_code'         => 'admin',
                    'store_storename'    => 'Admin',
                    'store_website_id'   => 0,
                    'store_website_code' => 'admin',
                    'store_website_name' => 'Admin',
                ],
                [
                    [
                        'website_id' => 0,
                        'code'       => 'admin',
                        'name'       => 'Admin',
                        'store_id'   => 0
                    ]
                ],
                [
                    [
                        'id'   => 0,
                        'code' => 'admin',
                        'name' => 'Admin',
                    ]
                ]
            ],
        ];
    }
}
