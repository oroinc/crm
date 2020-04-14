<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Converter;

use Oro\Bundle\MagentoBundle\ImportExport\Converter\OrderAddressDataConverter;
use Oro\Bundle\MagentoBundle\Provider\Iso2CodeProvider;

class OrderAddressDataConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Iso2CodeProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $iso2CodeProvider;

    /**
     * @var OrderAddressDataConverter
     */
    protected $dataConverter;

    protected function setUp(): void
    {
        $this->iso2CodeProvider = $this->getMockBuilder(Iso2CodeProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataConverter = new OrderAddressDataConverter();
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
            'order address' => [
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
                    'fax' => 'fax',
                    'customer_id' => 1,
                    'address_type' => 'billing',
                    'address_id' => 2,
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
                    'fax' => 'fax',
                    'customerId' => 1,
                    'types' => [
                        [
                            'name' => 'billing'
                        ]
                    ],
                    'originId' => 2,
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
     * @dataProvider importDataProviderWithCodeProvider
     * @param array $import
     * @param array $expected
     * @param string $foundCode
     */
    public function testConvertToImportFormatWithCodeProvider(array $import, array $expected, $foundCode)
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
    public function importDataProviderWithCodeProvider()
    {
        return [
            'order address without country_id' => [
                [
                    'country_id' => null,
                ],
                [
                    'region' => null,
                ],
                'NewFoundCode',
            ],
            'order address without foundCode' => [
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
            'order address with foundCode' => [
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
