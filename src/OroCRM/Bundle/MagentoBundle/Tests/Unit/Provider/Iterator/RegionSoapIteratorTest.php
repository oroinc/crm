<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\RegionSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class RegionSoapIteratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var RegionSoapIterator */
    protected $iterator;

    /** @var \PHPUnit_Framework_MockObject_MockObject|SoapTransport */
    protected $transport;

    public function setUp()
    {
        $this->transport = $this->getMockBuilder('OroCRM\\Bundle\\MagentoBundle\\Provider\\Transport\\SoapTransport')
            ->disableOriginalConstructor()->getMock();

        $settings       = ['start_sync_date' => new \DateTime('NOW'), 'website_id' => 0];
        $this->iterator = new RegionSoapIterator($this->transport, $settings);
    }

    public function tearDown()
    {
        unset($this->iterator, $this->transport);
    }

    public function testIteration()
    {
        $countries = [
            'UA' => (object)['iso2_code' => 'UA', 'name' => 'Ukraine'],
            'US' => (object)['iso2_code' => 'US', 'name' => 'USA'],
            'RU' => (object)['iso2_code' => 'RU', 'name' => 'Russia'],
        ];

        $regions = [
            'UA' => [],
            'US' => [
                (object)['code' => 'AZ', 'region_id' => 1, 'name' => 'Arizona'],
                (object)['code' => 'DE', 'region_id' => 2, 'name' => 'Delaware'],
            ],
            'RU' => [
                (object)['code' => 'MO', 'region_id' => 3, 'name' => 'Moskow'],
                (object)['code' => 'TU', 'region_id' => 4, 'name' => 'Tula'],
            ]
        ];

        $this->transport->expects($this->at(0))->method('call')
            ->with($this->equalTo('directoryCountryList'))->will($this->returnValue($countries));

        $i = 1;
        foreach ($regions as $countryCode => $regionsForCountry) {
            $this->transport->expects($this->at($i))->method('call')
                ->with($this->equalTo('directoryRegionList'), $this->equalTo([$countryCode]))
                ->will($this->returnValue($regionsForCountry));

            $i++;
        }

        $this->assertEquals(
            [
                'AZ' => [
                    'code'        => 'AZ',
                    'region_id'   => 1,
                    'name'        => 'Arizona',
                    'countryCode' => 'US',
                ],
                'DE' => [
                    'code'        => 'DE',
                    'region_id'   => 2,
                    'name'        => 'Delaware',
                    'countryCode' => 'US',
                ],
                'MO' => [
                    'code'        => 'MO',
                    'region_id'   => 3,
                    'name'        => 'Moskow',
                    'countryCode' => 'RU',
                ],
                'TU' => [
                    'code'        => 'TU',
                    'region_id'   => 4,
                    'name'        => 'Tula',
                    'countryCode' => 'RU',
                ],
            ],
            iterator_to_array($this->iterator)
        );
    }
}
