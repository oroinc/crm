<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;

use OroCRM\Bundle\MagentoBundle\ImportExport\Processor\ImportProcessor;
use OroCRM\Bundle\MagentoBundle\Tests\Unit\ImportExport\Processor\Stub\StepExecutionAwareStrategyStub;

class ImportProcessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ImportProcessor */
    protected $processor;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ContextRegistry */
    protected $contextRegistry;

    protected function setUp()
    {
        $this->contextRegistry = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = new ImportProcessor();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ContextRegistry is missing
     */
    public function testSetStepExecutionFailed()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|StepExecution $stepExecution */
        $stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor->setStepExecution($stepExecution);
    }

    public function testSetStepExecution()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|StepExecution $stepExecution */
        $stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|ContextRegistry $contextRegistry */
        $contextRegistry = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $contextRegistry->expects($this->once())
            ->method('getByStepExecution')
            ->will($this->returnValue($context));

        $this->processor->setContextRegistry($contextRegistry);
        $this->processor->setStepExecution($stepExecution);
    }

    public function testSetStepExecutionStrategyIsNotAware()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|StepExecution $stepExecution */
        $stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|ContextRegistry $contextRegistry */
        $contextRegistry = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $contextRegistry->expects($this->once())
            ->method('getByStepExecution')
            ->will($this->returnValue($context));

        /** @var \PHPUnit_Framework_MockObject_MockObject|StrategyInterface $strategy */
        $strategy = $this->getMock('Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface');
        $this->processor->setStrategy($strategy);
        $strategy->expects($this->never())->method('setStepExecution');

        $this->processor->setContextRegistry($contextRegistry);
        $this->processor->setStepExecution($stepExecution);
    }

    public function testSetStepExecutionStrategyIsAware()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|StepExecution $stepExecution */
        $stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|ContextRegistry $contextRegistry */
        $contextRegistry = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $contextRegistry->expects($this->once())
            ->method('getByStepExecution')
            ->will($this->returnValue($context));

        /** @var \PHPUnit_Framework_MockObject_MockObject|StepExecutionAwareStrategyStub $strategy */
        $strategy = $this->getMock(
            'OroCRM\Bundle\MagentoBundle\Tests\Unit\ImportExport\Processor\Stub\StepExecutionAwareStrategyStub'
        );
        $this->processor->setStrategy($strategy);
        $strategy->expects($this->once())->method('setStepExecution')->with($stepExecution);

        $this->processor->setContextRegistry($contextRegistry);
        $this->processor->setStepExecution($stepExecution);
    }
}
