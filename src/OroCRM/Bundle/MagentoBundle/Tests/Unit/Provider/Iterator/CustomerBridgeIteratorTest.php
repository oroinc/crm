<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\CustomerBridgeIterator;

class CustomerBridgeIteratorTest extends BaseIteratorTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->iterator = new CustomerBridgeIterator($this->transport, $this->settings);
    }

    /**
     * @param array $customerArray
     * @param array $storeData
     * @param array $stores
     * @param array $websites
     * @param array $groups
     *
     * @dataProvider dataProvider
     */
    public function testIteration(array $customerArray, array $storeData, array $stores, array $websites, array $groups)
    {
        $this->transport->expects($this->once())->method('call')
            ->with($this->equalTo('oroCustomerList'))
            ->will($this->returnValue($customerArray));

        $this->transport->expects($this->atLeastOnce())->method('getDependencies')
            ->will(
                $this->returnValue(
                    [
                        'stores' => new \ArrayIterator($stores),
                        'websites' => new \ArrayIterator($websites),
                        'groups' => new \ArrayIterator($groups)
                    ]
                )
            );

        $this->assertEquals(
            [
                1 => array_merge((array)$customerArray[0], $storeData),
                2 => array_merge((array)$customerArray[1], $storeData),
                3 => array_merge((array)$customerArray[2], $storeData)
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
                ],
                'storeData' => [
                    'group' => [
                        'id' => 0,
                        'name' => 'Admin',
                        'customer_group_id' => 0,
                        'originId' => 0
                    ],
                    'store' => [
                        'website_id' => 0,
                        'code' => 'admin',
                        'name' => 'Admin',
                        'store_id' => 0,
                        'originId' => 0
                    ],
                    'website' => [
                        'id' => 0,
                        'code' => 'admin',
                        'name' => 'Admin',
                        'originId' => 0
                    ]
                ],
                'stores' => [
                    [
                        'website_id' => 0,
                        'code' => 'admin',
                        'name' => 'Admin',
                        'store_id' => 0
                    ]
                ],
                'websites' => [
                    [
                        'id' => 0,
                        'code' => 'admin',
                        'name' => 'Admin'
                    ]
                ],
                'groups' => [
                    [
                        'id' => 0,
                        'name' => 'Admin',
                        'customer_group_id' => 0
                    ]
                ]
            ]
        ];
    }

    public function testConstructBatchSize()
    {
        $iterator = new CustomerBridgeIterator($this->transport, $this->settings);
        $this->assertAttributeEquals(CustomerBridgeIterator::DEFAULT_PAGE_SIZE, 'pageSize', $iterator);

        $batchSize = 2000;
        $settings = array_merge($this->settings, ['page_size' => $batchSize]);
        $iterator = new CustomerBridgeIterator($this->transport, $settings);
        $this->assertAttributeEquals($batchSize, 'pageSize', $iterator);
    }
}
