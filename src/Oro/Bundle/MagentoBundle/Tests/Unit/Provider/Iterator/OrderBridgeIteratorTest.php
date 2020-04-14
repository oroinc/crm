<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\OrderBridgeIterator;
use Oro\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class OrderBridgeIteratorTest extends BaseSoapIteratorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->iterator = new OrderBridgeIterator($this->transport, $this->settings);
    }

    /**
     * @param array $orderArray
     * @param array $stores
     *
     * @dataProvider dataProvider
     */
    public function testIteration(array $orderArray, array $stores)
    {
        $this->transport->expects($this->once())
            ->method('getStores')
            ->will($this->returnValue($stores));

        $this->transport->expects($this->once())
            ->method('call')
            ->with($this->equalTo(SoapTransport::ACTION_ORO_ORDER_LIST))
            ->will($this->returnValue($orderArray));

        $this->assertEquals(
            [
                1 => array_merge((array)$orderArray[0], ['items' => []]),
                2 => array_merge((array)$orderArray[1], ['items' => []]),
                3 => array_merge((array)$orderArray[2], ['items' => []])
            ],
            iterator_to_array($this->iterator)
        );
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $orderArray
     * @param string $stores
     */
    public function testUpdateMode($orderArray, $stores)
    {
        $this->iterator->setMode(UpdatedLoaderInterface::IMPORT_MODE_UPDATE);
        $this->testIteration($orderArray, $stores);
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
                    [
                        'website_id' => 0,
                        'code' => 'admin',
                        'name' => 'Admin',
                        'store_id' => 0
                    ]
                ]
            ]
        ];
    }
}
