<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\CustomerBridgeIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

class CustomerBridgeIteratorTest extends BaseIteratorTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->iterator = new CustomerBridgeIterator($this->transport, $this->settings);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testIteration($customerArray, $storeData, $stores, $websites, $groups)
    {
        $dependencies = [
            MagentoTransportInterface::ALIAS_STORES => $stores,
            MagentoTransportInterface::ALIAS_WEBSITES => $websites,
            MagentoTransportInterface::ALIAS_GROUPS => $groups
        ];
        $this->transport->expects($this->atLeastOnce())
            ->method('getDependencies')
            ->will($this->returnValue($dependencies));

        $this->transport->expects($this->atLeastOnce())
            ->method('call')
            ->with($this->equalTo('oroCustomerList'))
            ->will($this->returnValue($customerArray));

        $orders = [
            array_merge((array)$customerArray[0], $storeData),
            array_merge((array)$customerArray[1], $storeData),
            array_merge((array)$customerArray[2], $storeData),
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

    public function dataProvider()
    {
        return [
            'usual test case' => [
                // $customerArray
                [
                    (object)[
                        'customer_id' => 1,
                        'total'       => 12.5,
                        'store_id'    => 0,
                        'store_name'  => 'admin',
                        'group_id'    => 0,
                        'website_id'  => 0,
                        'addresses'   => [],
                    ],
                    (object)[
                        'customer_id' => 2,
                        'total'       => 132,
                        'store_id'    => 0,
                        'store_name'  => 'admin',
                        'group_id'    => 0,
                        'website_id'  => 0,
                        'addresses'   => [],
                    ],
                    (object)[
                        'customer_id' => 3,
                        'total'       => 86,
                        'store_id'    => 0,
                        'store_name'  => 'admin',
                        'group_id'    => 0,
                        'website_id'  => 0,
                        'addresses'   => [],
                    ]
                ],
                // $storeData
                [
                    'group' => [
                        'id'                => 0,
                        'name'              => 'Admin',
                        'customer_group_id' => 0,
                        'originId'          => 0,
                    ],
                    'store' => [
                        'website_id' => 0,
                        'code'       => 'admin',
                        'name'       => 'Admin',
                        'store_id'   => 0,
                        'originId'   => 0,
                    ],
                    'website' => [
                        'id'       => 0,
                        'code'     => 'admin',
                        'name'     => 'Admin',
                        'originId' => 0,
                    ],
                ],
                // $stores
                [
                    [
                        'website_id' => 0,
                        'code'       => 'admin',
                        'name'       => 'Admin',
                        'store_id'   => 0
                    ]
                ],
                // $websites
                [
                    [
                        'id'   => 0,
                        'code' => 'admin',
                        'name' => 'Admin',
                    ]
                ],
                // $groups
                [
                    [
                        'id'                => 0,
                        'name'              => 'Admin',
                        'customer_group_id' => 0,
                    ]
                ],
            ],
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
