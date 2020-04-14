<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Converter;

use Oro\Bundle\MagentoBundle\ImportExport\Converter\CartAddressDataConverter;
use Oro\Bundle\MagentoBundle\Provider\Iso2CodeProvider;

class CartAddressDataConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Iso2CodeProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $iso2CodeProvider;

    /**
     * @var CartAddressDataConverter
     */
    protected $dataConverter;

    protected function setUp(): void
    {
        $this->iso2CodeProvider = $this->getMockBuilder(Iso2CodeProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataConverter = new CartAddressDataConverter();
    }

    /**
     * @dataProvider importDataProvider
     * @param array $import
     * @param array $expected
     */
    public function testConvertToImportFormat(array $import, array $expected)
    {
        $result = $this->dataConverter->convertToImportFormat($import);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function importDataProvider()
    {
        return [
            'cart address' => [
                [
                    'created_at' => '2011-11-11',
                    'updated_at' => '2012-12-12',
                    'region_id' => 'AL',
                    'firstname' => 'firstName',
                    'lastname' => 'lastName',
                    'middlename' => 'middleName',
                    'prefix' => 'namePrefix',
                    'suffix' => 'nameSuffix',
                    'region' => 'regionText',
                    'country_id' => 'US',
                    'postcode' => 'postalCode',
                    'telephone' => 'phone',
                    'company' => 'organization',
                    'city' => 'city',
                    'street' => 'street',
                    'street2' => 'street2',
                ],
                [
                    'regionText' => 'regionText',
                    'firstName' => 'firstName',
                    'lastName' => 'lastName',
                    'middleName' => 'middleName',
                    'namePrefix' => 'namePrefix',
                    'nameSuffix' => 'nameSuffix',
                    'created' => '2011-11-11',
                    'updated' => '2012-12-12',
                    'postalCode' => 'postalCode',
                    'phone' => 'phone',
                    'countryText' => 'US',
                    'organization' => 'organization',
                    'city' => 'city',
                    'street' => 'street',
                    'street2' => 'street2',
                    'region' => [
                        'code' => 'AL'
                    ],
                    'country' => [
                        'iso2Code' => 'US'
                    ],
                ]
            ]
        ];
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
            'cart address without country_id' => [
                [
                    'country_id' => null,
                ],
                [
                    'region' => null,
                ],
                'NewFoundCode',
            ],
            'cart address without foundCode' => [
                [
                    'country_id' => 'US',
                ],
                [
                    'country' => null,
                    'region' => null,
                    'countryText' => 'US',
                ],
                null,
            ],
            'cart address with foundCode' => [
                [
                    'country_id' => 'US',
                ],
                [
                    'country' => [
                        'iso2Code' => 'NewFoundCode'
                    ],
                    'region' => null,
                    'countryText' => 'US',
                ],
                'NewFoundCode',
            ]
        ];
    }
}
