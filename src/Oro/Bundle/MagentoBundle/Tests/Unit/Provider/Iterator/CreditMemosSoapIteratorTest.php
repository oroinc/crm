<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\CreditMemoSoapIterator;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class CreditMemosSoapIteratorTest extends BaseSoapIteratorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->iterator = new CreditMemoSoapIterator($this->transport, $this->settings);
    }

    /**
     * @param array $data
     * @param array $stores
     *
     * @dataProvider dataProvider
     */
    public function testIteration(array $data, array $stores)
    {
        $this->transport->expects($this->once())
            ->method('getStores')
            ->will($this->returnValue($stores));
        $this->transport->expects($this->at(2))->method('call')
            ->with($this->equalTo(SoapTransport::ACTION_CREDIT_MEMO_LIST))
            ->will($this->returnValue($data));
        $this->transport->expects($this->at(4))->method('call')
            ->with($this->equalTo(SoapTransport::ACTION_CREDIT_MEMO_LIST))
            ->will($this->returnValue([]));

        $this->assertEquals(
            [
                1 => array_merge((array)$data[0], ['items' => []]),
                2 => array_merge((array)$data[1], ['items' => []]),
                3 => array_merge((array)$data[2], ['items' => []])
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
                [
                    (object)[
                        'creditmemo_id' => 1,
                        'increment_id' => '10000001',
                        'grand_total' => 12.5,
                        'store_id' => 0,
                        'store_name' => 'admin',
                        'items' => (object)[]
                    ],
                    (object)[
                        'creditmemo_id' => 2,
                        'increment_id' => '10000002',
                        'grand_total' => 132,
                        'store_id' => 0,
                        'store_name' => 'admin',
                        'items' => (object)[]
                    ],
                    (object)[
                        'creditmemo_id' => 3,
                        'increment_id' => '10000003',
                        'grand_total' => 86,
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
