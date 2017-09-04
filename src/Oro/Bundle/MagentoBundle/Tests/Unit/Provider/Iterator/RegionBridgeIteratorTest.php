<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\RegionBridgeIterator;

class RegionBridgeIteratorTest extends BaseSoapIteratorTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->iterator = new RegionBridgeIterator($this->transport, $this->settings);
    }

    /**
     * @param array $regionArray
     *
     * @dataProvider dataProvider
     */
    public function testIteration(array $regionArray)
    {
        $this->transport->expects($this->once())->method('call')
            ->with($this->equalTo('oroRegionList'))
            ->will($this->returnValue($regionArray));


        $expectedResult = [];
        foreach ($regionArray as $code => $region) {
            $expectedResult[$code] = (array)$region;
        }

        $this->assertEquals(
            $expectedResult,
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
                'regionArray' => [
                    1 => (object)[
                        'code' => 'AZ',
                        'region_id' => 1,
                        'name' => 'Arizona',
                        'countryCode' => 'US'
                    ],
                    2 => (object)[
                        'code' => 'DE',
                        'region_id' => 2,
                        'name' => 'Delaware',
                        'countryCode' => 'US'
                    ],
                    3 => (object)[
                        'code' => 'MO',
                        'region_id' => 3,
                        'name' => 'Moscow',
                        'countryCode' => 'RU'
                    ],
                    4 => (object)[
                        'code' => 'TU',
                        'region_id' => 4,
                        'name' => 'Tula',
                        'countryCode' => 'RU'
                    ]
                ]
            ]
        ];
    }

    public function testConstructBatchSize()
    {
        $iterator = new RegionBridgeIterator($this->transport, $this->settings);
        $this->assertAttributeEquals(RegionBridgeIterator::DEFAULT_REGION_PAGE_SIZE, 'pageSize', $iterator);

        $batchSize = 2000;
        $settings = array_merge($this->settings, ['page_size' => $batchSize]);
        $iterator = new RegionBridgeIterator($this->transport, $settings);
        $this->assertAttributeEquals($batchSize, 'pageSize', $iterator);
    }
}
