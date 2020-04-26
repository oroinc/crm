<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CustomerBridgeIterator;

class CustomerBridgeIteratorTest extends BaseSoapIteratorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->iterator = new CustomerBridgeIterator($this->transport, $this->settings);
    }

    /**
     * @param array $customerArray
     *
     * @dataProvider dataProvider
     */
    public function testIteration(array $customerArray)
    {
        $this->transport->expects($this->once())->method('call')
            ->with($this->equalTo('oroCustomerList'))
            ->will($this->returnValue($customerArray));

        $this->assertEquals(
            [
                1 => (array)$customerArray[0],
                2 => (array)$customerArray[1],
                3 => (array)$customerArray[2]
            ],
            iterator_to_array($this->iterator)
        );
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'usual test case' => [
                'customerArray' => [
                    (object)[
                        'customer_id' => 1,
                        'total' => 12.5,
                        'store_id' => 0,
                        'store_name' => 'admin',
                        'group_id' => 0,
                        'website_id' => 0,
                        'addresses' => []
                    ],
                    (object)[
                        'customer_id' => 2,
                        'total' => 132,
                        'store_id' => 0,
                        'store_name' => 'admin',
                        'group_id' => 0,
                        'website_id' => 0,
                        'addresses' => []
                    ],
                    (object)[
                        'customer_id' => 3,
                        'total' => 86,
                        'store_id' => 0,
                        'store_name' => 'admin',
                        'group_id' => 0,
                        'website_id' => 0,
                        'addresses' => []
                    ]
                ]
            ]
        ];
    }

    public function testConstructBatchSize()
    {
        $iterator1 = new class($this->transport, $this->settings) extends CustomerBridgeIterator {
            public function xgetPageSize(): int
            {
                return $this->pageSize;
            }
        };

        $batchSize = 2000;
        $customSettings = \array_merge($this->settings, ['page_size' => $batchSize]);

        $iterator2 = new class($this->transport, $customSettings) extends CustomerBridgeIterator {
            public function xgetPageSize(): int
            {
                return $this->pageSize;
            }
        };

        static::assertEquals(CustomerBridgeIterator::DEFAULT_PAGE_SIZE, $iterator1->xgetPageSize());
        static::assertEquals($batchSize, $iterator2->xgetPageSize());
    }
}
