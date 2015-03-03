<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\OrderBridgeIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

class OrderBridgeIteratorTest extends BaseIteratorTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->iterator = new OrderBridgeIterator($this->transport, $this->settings);
    }

    /**
     * @param array $orderArray
     * @param array $storeData
     * @param array $stores
     * @param array $websites
     *
     * @dataProvider dataProvider
     */
    public function testIteration(array $orderArray, array $storeData, array $stores, array $websites)
    {
        $dependencies = [
            MagentoTransportInterface::ALIAS_STORES => $stores,
            MagentoTransportInterface::ALIAS_WEBSITES => $websites
        ];
        $this->transport->expects($this->atLeastOnce())
            ->method('getDependencies')
            ->will($this->returnValue($dependencies));

        $this->transport->expects($this->once())->method('call')
            ->with($this->equalTo('oroOrderList'))
            ->will($this->returnValue($orderArray));

        $this->assertEquals(
            [
                1 => array_merge((array)$orderArray[0], $storeData, ['items' => []]),
                2 => array_merge((array)$orderArray[1], $storeData, ['items' => []]),
                3 => array_merge((array)$orderArray[2], $storeData, ['items' => []])
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

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'one test case' => [
                [
                    (object)[
                        'order_id' => 1,
                        'total' => 12.5,
                        'store_id' => 0,
                        'store_name' => 'admin',
                        'items' => (object)[]
                    ],
                    (object)[
                        'order_id' => 2,
                        'total' => 132,
                        'store_id' => 0,
                        'store_name' => 'admin',
                        'items' => (object)[]
                    ],
                    (object)[
                        'order_id' => 3,
                        'total' => 86,
                        'store_id' => 0,
                        'store_name' => 'admin',
                        'items' => (object)[]
                    ]
                ],
                [
                    'store_code' => 'admin',
                    'store_storename' => 'Admin',
                    'store_website_id' => 0,
                    'store_website_code' => 'admin',
                    'store_website_name' => 'Admin'
                ],
                [
                    [
                        'website_id' => 0,
                        'code' => 'admin',
                        'name' => 'Admin',
                        'store_id' => 0
                    ]
                ],
                [
                    [
                        'id' => 0,
                        'code' => 'admin',
                        'name' => 'Admin'
                    ]
                ]
            ]
        ];
    }
}
