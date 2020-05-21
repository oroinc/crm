<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Converter;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\MagentoBundle\ImportExport\Converter\NewsletterSubscriberDataConverter;

class NewsletterSubscriberDataConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NewsletterSubscriberDataConverter
     */
    protected $dataConverter;

    protected function setUp(): void
    {
        $this->dataConverter = new NewsletterSubscriberDataConverter();
    }

    public function testConvertToExportFormat()
    {
        $result = $this->dataConverter->convertToExportFormat(['originId' => '1']);
        $this->assertArrayHasKey('subscriber_id', $result);

        $this->assertEquals($result['subscriber_id'], '1');
    }

    public function testConvertToImportFormat()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|ContextInterface $context */
        $context = $this->createMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $context->expects($this->atLeastOnce())->method('hasOption')->with($this->isType('string'))->willReturn(true);
        $context->expects($this->atLeastOnce())->method('getOption')->with($this->isType('string'))->willReturn(1);
        $this->dataConverter->setImportExportContext($context);

        $result = $this->dataConverter->convertToImportFormat(['subscriber_id' => '1']);
        $this->assertArrayHasKey('originId', $result);
        $this->assertArrayHasKey('customer', $result);
        $this->assertArrayHasKey('channel', $result);

        $this->assertEquals($result['originId'], '1');
    }
}
