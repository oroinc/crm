<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Converter;

use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\CustomerAddressDataConverter;
use OroCRM\Bundle\MagentoBundle\Provider\Iso2CodeProvider;

class CustomerAddressDataConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Iso2CodeProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $iso2CodeProvider;

    /**
     * @var CustomerAddressDataConverter
     */
    protected $dataConverter;

    protected function setUp()
    {
        $this->iso2CodeProvider = $this->getMockBuilder(Iso2CodeProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataConverter = new CustomerAddressDataConverter();
    }

    /**
     * @dataProvider importDataProviderWithIso2CodeProvider
     * @param array $import
     * @param array $expected
     * @param string $foundCode
     */
    public function testConvertToImportFormatWithIso2CodeProvider(array $import, array $expected, $foundCode)
    {
        $this->dataConverter->setIso2CodeProvider($this->iso2CodeProvider);
        $this->iso2CodeProvider->expects($this->any())
            ->method('getIso2CodeByCountryId')
            ->with($import['country_id'])
            ->willReturn($foundCode);
        $result = $this->dataConverter->convertToImportFormat($import);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function importDataProviderWithIso2CodeProvider()
    {
        return [
            'customer address without country_id' => [
                [
                    'country_id' => null,
                ],
                [
                    'region' => null,
                ],
                'NewFoundCode',
            ],
            'customer address without foundCode' => [
                [
                    'country_id' => 'US',
                ],
                [
                    'country' => null,
                    'region' => null,
                ],
                null,
            ],
            'customer address with foundCode' => [
                [
                    'country_id' => 'US',
                ],
                [
                    'country' => [
                        'iso2Code' => 'NewFoundCode'
                    ],
                    'region' => null,
                ],
                'NewFoundCode',
            ]
        ];
    }
}
