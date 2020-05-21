<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Converter;

use Oro\Bundle\MagentoBundle\ImportExport\Converter\CustomerDataConverter;

class CustomerDataConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CustomerDataConverter
     */
    protected $dataConverter;

    protected function setUp(): void
    {
        $this->dataConverter = new CustomerDataConverter();
    }

    public function testConvertToExportFormat()
    {
        $result = $this->dataConverter->convertToExportFormat(['firstName' => 'test']);
        $this->assertArrayHasKey('firstname', $result);

        $this->assertEquals($result['firstname'], 'test');
    }

    public function testConvertToImportFormat()
    {
        $result = $this->dataConverter->convertToImportFormat(['firstname' => 'test']);
        $this->assertArrayHasKey('firstName', $result);

        $this->assertEquals($result['firstName'], 'test');
    }
}
