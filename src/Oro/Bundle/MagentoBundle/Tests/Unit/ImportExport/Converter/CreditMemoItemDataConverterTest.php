<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Importexport\Processor;

use Oro\Bundle\MagentoBundle\ImportExport\Converter\CreditMemoItemDataConverter;

class CreditMemoItemDataConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CreditMemoItemDataConverter
     */
    protected $dataConverter;

    protected function setUp()
    {
        $this->dataConverter = new CreditMemoItemDataConverter();
    }

    public function testConvertToImportFormat()
    {
        $result = $this->dataConverter->convertToImportFormat(
            [
                'item_id' => '1',
                'sku' => 'sku',
            ]
        );

        $this->assertArrayHasKey('originId', $result);
        $this->assertArrayHasKey('sku', $result);

        $this->assertEquals($result['originId'], '1');
        $this->assertEquals($result['sku'], 'sku');
    }
}
