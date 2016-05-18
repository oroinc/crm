<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Processor;

use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\WebsiteDataConverter;

class WebsiteDataConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WebsiteDataConverter
     */
    protected $dataConverter;

    protected function setUp()
    {
        $this->dataConverter = new WebsiteDataConverter();
    }

    public function testConvertToImportFormat()
    {
        $result = $this->dataConverter->convertToImportFormat(['name' => 'test', 'code' => 'test_test']);

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('code', $result);

        $this->assertEquals($result['name'], 'test');
        $this->assertEquals($result['code'], 'test_test');
    }

    public function testConvertToImportFormatWithLongName()
    {
        $longName = str_pad('test', 300, ', test', STR_PAD_RIGHT);
        $longCode = str_pad('test', 300, '/ test', STR_PAD_RIGHT);

        $result = $this->dataConverter->convertToImportFormat(['name' => $longName, 'code' => $longCode]);

        $this->assertEquals(mb_strlen($result['name']), 255);
        $this->assertEquals(mb_strlen($result['code']), 32);
    }
}
