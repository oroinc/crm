<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\ImportExport\Writer;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\ImportExport\Writer\CustomerExportWriter;

class CustomerExportWriterTest extends AbstractExportWriterTest
{
    protected function setUp()
    {
        $this->markTestIncomplete('CRM-2411');
        parent::setUp();

        $this->writer = new CustomerExportWriter(
            $this->registry,
            $this->eventDispatcher,
            $this->contextRegistry,
            $this->logger
        );
    }

    public function testWriteWillUpdateCustomer()
    {
        $channel = new Channel();
        $transportEntity = new MagentoSoapTransport();
        $channel->setTransport($transportEntity);

        $transport = $this->getMock('OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface');
        $this->writer->setTransport($transport);

        $stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository->expects($this->any())->method('find')
            ->will($this->returnValue($channel));

        $this->registry->expects($this->any())->method('getRepository')
            ->will($this->returnValue($repository));

        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $context->expects($this->atLeastOnce())->method('hasOption')->will(
            $this->returnValueMap(
                [
                    ['channel', true],
                ]
            )
        );
        $context->expects($this->atLeastOnce())->method('getOption')->will(
            $this->returnValueMap(
                [
                    ['channel', null, 1],
                ]
            )
        );

        $this->contextRegistry->expects($this->atLeastOnce())
            ->method('getByStepExecution')
            ->with($stepExecution)
            ->will($this->returnValue($context));

        $this->writer->setStepExecution($stepExecution);

        $expectedData = ['customer_id' => 1, 'firstname' => 'John'];

        $transport->expects($this->once())
            ->method('updateCustomer')
            ->with($this->equalTo(1), $this->equalTo($expectedData))
            ->will($this->returnValue(true));

        $this->writer->write([$expectedData]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Option "entity" was not configured
     */
    public function testWriteWillCreateCustomerFailed()
    {
        $channel = new Channel();
        $transportEntity = new MagentoSoapTransport();
        $channel->setTransport($transportEntity);

        $transport = $this->getMock('OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface');
        $this->writer->setTransport($transport);

        $stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository->expects($this->any())->method('find')
            ->will($this->returnValue($channel));

        $this->registry->expects($this->any())->method('getRepository')
            ->will($this->returnValue($repository));

        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $context->expects($this->atLeastOnce())->method('hasOption')->will(
            $this->returnValueMap(
                [
                    ['channel', true],
                    ['entity', false],
                ]
            )
        );
        $context->expects($this->atLeastOnce())->method('getOption')->will(
            $this->returnValueMap(
                [
                    ['channel', null, 1],
                ]
            )
        );

        $this->contextRegistry->expects($this->atLeastOnce())
            ->method('getByStepExecution')
            ->with($stepExecution)
            ->will($this->returnValue($context));

        $this->writer->setStepExecution($stepExecution);

        $expectedData = ['customer_id' => null, 'firstname' => 'John'];

        $transport->expects($this->once())
            ->method('createCustomer')
            ->with($this->equalTo($expectedData))
            ->will($this->returnValue(1));

        $this->writer->write([$expectedData]);
    }

    public function testWriteWillCreateCustomer()
    {
        $channel = new Channel();
        $transportEntity = new MagentoSoapTransport();
        $channel->setTransport($transportEntity);

        $transport = $this->getMock('OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface');
        $this->writer->setTransport($transport);

        $stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository->expects($this->any())->method('find')
            ->will($this->returnValue($channel));

        $this->registry->expects($this->any())->method('getRepository')
            ->will($this->returnValue($repository));

        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');

        $entity = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->atLeastOnce())->method('hasOption')->will(
            $this->returnValueMap(
                [
                    ['channel', true],
                    ['entity', true],
                ]
            )
        );
        $context->expects($this->atLeastOnce())->method('getOption')->will(
            $this->returnValueMap(
                [
                    ['channel', null, 1],
                    ['entity', null, $entity],
                ]
            )
        );

        $this->contextRegistry->expects($this->atLeastOnce())
            ->method('getByStepExecution')
            ->with($stepExecution)
            ->will($this->returnValue($context));

        $this->writer->setStepExecution($stepExecution);

        $expectedData = ['customer_id' => null, 'firstname' => 'John'];

        $transport->expects($this->once())
            ->method('createCustomer')
            ->with($this->equalTo($expectedData))
            ->will($this->returnValue(2));

        $entity->expects($this->once())->method('setOriginId')->with($this->equalTo(2));

        $this->writer->write([$expectedData]);
    }
}
