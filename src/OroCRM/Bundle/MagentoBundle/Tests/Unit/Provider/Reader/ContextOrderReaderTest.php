<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Reader;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use OroCRM\Bundle\MagentoBundle\Provider\Reader\ContextOrderReader;

class ContextOrderReaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContextOrderReader */
    protected $reader;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ContextRegistry */
    protected $contextRegistry;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ExecutionContext */
    protected $executionContext;

    /** @var \PHPUnit_Framework_MockObject_MockObject|JobExecution */
    protected $jobExecution;

    protected function setUp()
    {
        $this->contextRegistry = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->reader = new ContextOrderReader($this->contextRegistry);
    }

    public function testReadEmpty()
    {
        $this->assertEmpty($this->reader->read());
    }

    public function testInitializeAndRead()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|StepExecution $stepExecution */
        $stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');

        $this->contextRegistry->expects($this->once())
            ->method('getByStepExecution')
            ->with($stepExecution)
            ->will($this->returnValue($context));

        $this->executionContext = $this->getMock('Akeneo\Bundle\BatchBundle\Item\ExecutionContext');

        $this->jobExecution = $this->getMock('Akeneo\Bundle\BatchBundle\Entity\JobExecution');
        $this->jobExecution->expects($this->any())
            ->method('getExecutionContext')
            ->will($this->returnValue($this->executionContext));

        $orderData = [['customer' => ['originId' => 1]], ['customer' => ['originId' => 2]]];
        $this->executionContext->expects($this->once())
            ->method('get')
            ->will($this->returnValue($orderData));

        $stepExecution->expects($this->once())
            ->method('getJobExecution')
            ->will($this->returnValue($this->jobExecution));

        $this->reader->setStepExecution($stepExecution);

        $this->assertEquals(reset($orderData), $this->reader->read());
        $this->assertEquals(end($orderData), $this->reader->read());
    }
}
