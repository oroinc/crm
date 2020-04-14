<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Writer;

use Oro\Bundle\IntegrationBundle\Tests\Unit\ImportExport\Writer\PersistentBatchWriterTest;
use Oro\Bundle\MagentoBundle\ImportExport\Writer\AbstractExportWriter;

abstract class AbstractExportWriterTest extends PersistentBatchWriterTest
{
    /**
     * @var AbstractExportWriter
     */
    protected $writer;

    public function testChannelIdMissing()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Channel id is missing');

        $transport = $this->createMock('Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface');
        $this->writer->setTransport($transport);

        $stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->createMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $this->contextRegistry->expects($this->once())
            ->method('getByStepExecution')
            ->with($stepExecution)
            ->will($this->returnValue($context));

        $context->expects($this->once())
            ->method('hasOption')
            ->with($this->equalTo('channel'))
            ->will($this->returnValue(false));

        $this->writer->setStepExecution($stepExecution);

        $this->writer->write([['customer_id' => 1]]);
    }

    public function testChannelMissing()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Channel is missing');

        $transport = $this->createMock('Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface');
        $this->writer->setTransport($transport);

        $stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->createMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository->expects($this->any())->method('find')
            ->will($this->returnValue(null));

        $this->registry->expects($this->any())->method('getRepository')
            ->will($this->returnValue($repository));

        $context = $this->createMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $context->expects($this->once())
            ->method('getOption')
            ->with($this->equalTo('channel'))
            ->will($this->returnValue(1));
        $context->expects($this->once())
            ->method('hasOption')
            ->with($this->equalTo('channel'))
            ->will($this->returnValue(true));

        $this->contextRegistry->expects($this->atLeastOnce())
            ->method('getByStepExecution')
            ->with($stepExecution)
            ->will($this->returnValue($context));

        $this->writer->setStepExecution($stepExecution);

        $this->writer->write([['customer_id' => 1]]);
    }
}
