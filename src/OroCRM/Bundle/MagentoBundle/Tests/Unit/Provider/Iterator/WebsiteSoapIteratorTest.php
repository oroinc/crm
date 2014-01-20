<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use Oro\Bundle\IntegrationBundle\Provider\SOAPTransport;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\StoresSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\WebsiteSoapIterator;

class WebsiteSoapIteratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var StoresSoapIterator */
    protected $iterator;

    /** @var \PHPUnit_Framework_MockObject_MockObject|SoapTransport */
    protected $transport;

    public function setUp()
    {
        $this->transport = $this->getMockBuilder('OroCRM\\Bundle\\MagentoBundle\\Provider\\Transport\\SoapTransport')
            ->disableOriginalConstructor()->getMock();

        $this->iterator = new WebsiteSoapIterator($this->transport);
    }

    public function tearDown()
    {
        unset($this->iterator, $this->transport);
    }

    /**
     * @dataProvider iterationProvider
     *
     * @param array $storesList
     * @param mixed $expectedResult
     */
    public function testIteration($storesList, $expectedResult)
    {
        $this->transport->expects($this->once())->method('getStores')
            ->will($this->returnValue($storesList));

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
            'bad data retrieved, should be empty array' => [[], []],
            'data retrieved correctly'                  => [
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
                ],
                [
                    0 => [
                        'name' => 'Admin',
                        'code' => 'admin',
                        'id'   => 0
                    ],
                    1 => [
                        'name' => 'B2C French',
                        'code' => 'fr_b2c',
                        'id'   => 1
                    ]
                ]
            ],
            'multiple stores for website'               => [
                [
                    1 => [
                        'store_id'   => 1,
                        'code'       => 'fr_b2b',
                        'website_id' => 1,
                        'name'       => 'B2B French'
                    ],
                    2 => [
                        'store_id'   => 2,
                        'code'       => 'fr_b2c',
                        'website_id' => 1,
                        'name'       => 'B2C French'
                    ]
                ],
                [
                    1 => [
                        'name' => 'B2B French, B2C French',
                        'code' => 'fr_b2b / fr_b2c',
                        'id'   => 1
                    ]
                ]
            ]
        ];
    }
}
