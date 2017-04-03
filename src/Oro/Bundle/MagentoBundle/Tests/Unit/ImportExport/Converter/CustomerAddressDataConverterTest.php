<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Importexport\Converter;

use Oro\Bundle\MagentoBundle\ImportExport\Converter\CustomerAddressDataConverter;

class CustomerAddressDataConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerAddressDataConverter
     */
    protected $dataConverter;

    protected function setUp()
    {
        $this->dataConverter = new CustomerAddressDataConverter();
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
            'customer address' => [
                [
                    'customer_address_id' => 1,
                    'customer_id' => 2,
                    'region_id' => 'AL',
                    'firstname' => 'firstName',
                    'lastname' => 'lastName',
                    'middlename' => 'middleName',
                    'prefix' => 'namePrefix',
                    'suffix' => 'nameSuffix',
                    'region' => 'regionText',
                    'country_id' => 'US',
                    'created_at' => '2011-11-11',
                    'updated_at' => '2012-12-12',
                    'postcode' => 'postalCode',
                    'telephone' => 'phone',
                    'company' => 'organization',
                    'city' => 'city',
                    'street' => 'street',
                    'street2' => 'street2',
                    'is_default_shipping' => true,
                    'is_default_billing' => true
                ],
                [
                    'originId' => 1,
                    'firstName' => 'firstName',
                    'lastName' => 'lastName',
                    'middleName' => 'middleName',
                    'namePrefix' => 'namePrefix',
                    'nameSuffix' => 'nameSuffix',
                    'regionText' => 'regionText',
                    'created' => '2011-11-11',
                    'updated' => '2012-12-12',
                    'postalCode' => 'postalCode',
                    'phone' => 'phone',
                    'organization' => 'organization',
                    'city' => 'city',
                    'street' => 'street',
                    'street2' => 'street2',
                    'countryText' => 'US',
                    'owner' => [
                        'originId' => 2
                    ],
                    'region' => [
                        'combinedCode' => 'AL'
                    ],
                    'country' => [
                        'iso2Code' => 'US'
                    ],
                    'types' => [
                        [
                            'name' => 'shipping'
                        ],
                        [
                            'name' => 'billing'
                        ]
                    ]
                ]
            ]
        ];
    }
}
