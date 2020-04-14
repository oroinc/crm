<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\StoresSoapIterator;

class StoresSoapIteratorTest extends BaseSoapIteratorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->iterator = new StoresSoapIterator($this->transport);
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
