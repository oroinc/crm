<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\StoresSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class StoresSoapIteratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var StoresSoapIterator */
    protected $iterator;

    /** @var \PHPUnit_Framework_MockObject_MockObject|SoapTransport */
    protected $transport;

    public function setUp()
    {
        $this->transport = $this->getMockBuilder('OroCRM\\Bundle\\MagentoBundle\\Provider\\Transport\\SoapTransport')
            ->disableOriginalConstructor()->getMock();

        $this->iterator = new StoresSoapIterator($this->transport);
    }

    public function tearDown()
    {
        unset($this->iterator, $this->transport);
    }

    /**
     * @dataProvider iterationProvider
     *
     * @param mixed $data
     * @param mixed $expectedResult
     */
    public function testIteration($data, $expectedResult)
    {
        $this->transport->expects($this->once())->method('call')
            ->with($this->equalTo('storeList'))->will($this->returnValue($data));

        $expectedKeys   = array_keys($expectedResult);
        $expectedValues = array_values($expectedResult);
        $keys           = $values = [];
        foreach ($this->iterator as $key => $value) {
            $keys[]   = $key;
            $values[] = $value;
        }

        $this->assertSame($expectedKeys, $keys, 'Should return correct keys');
        $this->assertSame($expectedValues, $values, 'Should return correct values');
        $this->assertSame($expectedResult, iterator_to_array($this->iterator));
    }

    /**
     * @return array
     */
    public function iterationProvider()
    {
        return [
            'bad data retrieved, should be empty array' => [null, []],
            'data retrieved correctly'                  => [
                [
                    (object)[
                        'store_id'   => 2,
                        'code'       => 'fr_b2c',
                        'website_id' => 1,
                        'group_id'   => 2,
                        'name'       => 'B2C French',
                        'sort_order' => 0,
                        'is_active'  => 1
                    ]
                ],
                [
                    0 => [
                        'website_id' => 0,
                        'code'       => 'admin',
                        'name'       => 'Admin',
                        'store_id'   => 0
                    ],
                    2 => [
                        'store_id'   => 2,
                        'code'       => 'fr_b2c',
                        'website_id' => 1,
                        'name'       => 'B2C French'
                    ]
                ]
            ]
        ];
    }
}
