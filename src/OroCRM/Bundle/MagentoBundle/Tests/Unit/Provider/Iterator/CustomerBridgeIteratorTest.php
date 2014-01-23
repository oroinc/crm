<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\CustomerBridgeIterator;

class CustomerBridgeIteratorTest extends BaseIteratorTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->iterator = new CustomerBridgeIterator($this->transport, $this->settings);
    }

    public function testIteration()
    {
        $orderArray = [
            (object)['order_id' => 1, 'total' => 12.5, 'store_id' => 0, 'store_name' => 'admin'],
            (object)['order_id' => 2, 'total' => 132,  'store_id' => 0, 'store_name' => 'admin'],
            (object)['order_id' => 3, 'total' => 86,   'store_id' => 0, 'store_name' => 'admin']
        ];

        $storeData = [
            'store_code'         => 'admin',
            'store_storename'    => 'admin',
            'store_website_id'   => 0,
            'store_website_code' => 'admin',
            'store_website_name' => 'Admin',
        ];

        $stores = [
            [
                'website_id' => 0,
                'code'       => 'admin',
                'name'       => 'Admin',
                'store_id'   => 0
            ]
        ];

        $websites = [
            [
                'id'   => 0,
                'code' => 'admin',
                'name' => 'Admin',
            ]
        ];

        $this->transport->expects($this->at(0))->method('getStores')
            ->will(
                $this->returnValue(new \ArrayIterator($stores))
            );

        $this->transport->expects($this->at(1))->method('getWebsites')
            ->will(
                $this->returnValue(new \ArrayIterator($websites))
            );

        $this->transport->expects($this->at(2))->method('getCustomerGroups')
            ->will(
                $this->returnValue(new \ArrayIterator($websites))
            );

        $this->transport->expects($this->at(3))->method('call')
            ->with($this->equalTo('oroOrderList'))
            ->will($this->returnValue($orderArray));

        $this->transport->expects($this->at(4))->method('call')
            ->with($this->equalTo('oroOrderList'))
            ->will($this->returnValue([]));

        $orders = [
            array_merge((array)$orderArray[0], $storeData),
            array_merge((array)$orderArray[1], $storeData),
            array_merge((array)$orderArray[2], $storeData),
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
}
