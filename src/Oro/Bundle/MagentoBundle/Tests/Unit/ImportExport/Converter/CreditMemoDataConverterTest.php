<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Importexport\Processor;

use Oro\Bundle\MagentoBundle\ImportExport\Converter\CreditMemoDataConverter;

class CreditMemoDataConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CreditMemoDataConverter
     */
    protected $dataConverter;

    protected function setUp(): void
    {
        $this->dataConverter = new CreditMemoDataConverter();
    }

    public function testConvertToImportFormat()
    {
        $context = $this->createMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $context->expects($this->atLeastOnce())->method('hasOption')->with('channel')->willReturn(true);
        $context->expects($this->atLeastOnce())->method('getOption')->with('channel')->willReturn(1);
        $this->dataConverter->setImportExportContext($context);

        $result = $this->dataConverter->convertToImportFormat(
            [
                'creditmemo_id' => '1',
                'increment_id' => '123',
                'order_id' => '2',
                'items' => [
                    'item_id' => 1,
                    'price' => 100
                ]
            ]
        );

        $this->assertArrayHasKey('originId', $result);
        $this->assertArrayHasKey('incrementId', $result);
        $this->assertArrayHasKey('order', $result);

        $this->assertEquals($result['originId'], '1');
        $this->assertEquals($result['incrementId'], '123');
        $this->assertEquals($result['order']['originId'], '2');
        $this->assertEquals($result['store']['channel']['id'], 1);

        $this->assertEquals(
            [
                [
                    'item_id' => 1,
                    'price' => 100
                ]
            ],
            $result['items']
        );
    }
}
