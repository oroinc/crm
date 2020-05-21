<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\RegionBridgeIterator;

class RegionBridgeIteratorTest extends BaseSoapIteratorTestCase
{
    protected function setUp(): void
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
        $iterator1 = new class($this->transport, $this->settings) extends RegionBridgeIterator {
            public function getPageSize(): int
            {
                return $this->pageSize;
            }
        };

        $batchSize = 2000;
        $customSettings = \array_merge($this->settings, ['page_size' => $batchSize]);

        $iterator2 = new class($this->transport, $customSettings) extends RegionBridgeIterator {
            public function getPageSize(): int
            {
                return $this->pageSize;
            }
        };

        static::assertEquals(RegionBridgeIterator::DEFAULT_REGION_PAGE_SIZE, $iterator1->getPageSize());
        static::assertEquals($batchSize, $iterator2->getPageSize());
    }
}
