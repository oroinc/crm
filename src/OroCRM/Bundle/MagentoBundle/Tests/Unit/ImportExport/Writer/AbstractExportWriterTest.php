<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\ImportExport\Writer;

use Oro\Bundle\IntegrationBundle\Tests\Unit\ImportExport\Writer\PersistentBatchWriterTest;
use OroCRM\Bundle\MagentoBundle\ImportExport\Writer\AbstractExportWriter;

abstract class AbstractExportWriterTest extends PersistentBatchWriterTest
{
    /**
     * @var AbstractExportWriter
     */
    protected $writer;

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Channel id is missing
     */
    public function testChannelIdMissing()
    {
        $transport = $this->getMock('OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface');
        $this->writer->setTransport($transport);

        $stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Channel is missing
     */
    public function testChannelMissing()
    {
        $transport = $this->getMock('OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface');
        $this->writer->setTransport($transport);

        $stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository->expects($this->any())->method('find')
            ->will($this->returnValue(null));

        $this->registry->expects($this->any())->method('getRepository')
            ->will($this->returnValue($repository));

        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
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
