<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\RegionSoapIterator;

class RegionSoapIteratorTest extends BaseSoapIteratorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->iterator = new RegionSoapIterator($this->transport, $this->settings);
    }

    public function testIteration()
    {
        $countries = [
            'UA' => (object)['iso2_code' => 'UA', 'name' => 'Ukraine'],
            'US' => (object)['iso2_code' => 'US', 'name' => 'USA'],
            'RU' => (object)['iso2_code' => 'RU', 'name' => 'Russia']
        ];

        $regions = [
            'UA' => [],
            'US' => [
                (object)['code' => 'AZ', 'region_id' => 1, 'name' => 'Arizona'],
                (object)['code' => 'DE', 'region_id' => 2, 'name' => 'Delaware'],
            ],
            'RU' => [
                (object)['code' => 'MO', 'region_id' => 3, 'name' => 'Moscow'],
                (object)['code' => 'TU', 'region_id' => 4, 'name' => 'Tula']
            ]
        ];

        $this->transport->expects($this->at(0))->method('call')
            ->with($this->equalTo('directoryCountryList'))->will($this->returnValue($countries));

        $i = 1;
        foreach ($regions as $countryCode => $regionsForCountry) {
            $this->transport->expects($this->at($i))->method('call')
                ->with($this->equalTo('directoryRegionList'), $this->equalTo(['country' => $countryCode]))
                ->will($this->returnValue($regionsForCountry));

            $i++;
        }

        $this->assertEquals(
            [
                'AZ' => [
                    'code' => 'AZ',
                    'region_id' => 1,
                    'name' => 'Arizona',
                    'countryCode' => 'US'
                ],
                'DE' => [
                    'code' => 'DE',
                    'region_id' => 2,
                    'name' => 'Delaware',
                    'countryCode' => 'US'
                ],
                'MO' => [
                    'code' => 'MO',
                    'region_id' => 3,
                    'name' => 'Moscow',
                    'countryCode' => 'RU'
                ],
                'TU' => [
                    'code' => 'TU',
                    'region_id' => 4,
                    'name' => 'Tula',
                    'countryCode' => 'RU'
                ]
            ],
            iterator_to_array($this->iterator)
        );
    }
}
