<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Reader;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Psr\Log\NullLogger;

abstract class AbstractInfoReaderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|ContextRegistry */
    protected $contextRegistry;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ConnectorContextMediator */
    protected $contextMediator;

    /** @var \PHPUnit\Framework\MockObject\MockObject|StepExecution */
    protected $stepExecutionMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ContextInterface */
    protected $context;

    /** @var \PHPUnit\Framework\MockObject\MockObject|MagentoTransportInterface */
    protected $transport;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ExecutionContext */
    protected $jobExecution;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ExecutionContext */
    protected $executionContext;

    /** @var LoggerStrategy */
    protected $logger;

    protected function setUp(): void
    {
        $this->contextRegistry = $this->createMock('Oro\Bundle\ImportExportBundle\Context\ContextRegistry');

        $this->logger = new LoggerStrategy(new NullLogger());

        $this->contextMediator = $this
            ->getMockBuilder('Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->stepExecutionMock = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->createMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');

        $this->contextRegistry->expects($this->any())
            ->method('getByStepExecution')
            ->will($this->returnValue($this->context));

        $channel = $this->createMock('Oro\Bundle\IntegrationBundle\Entity\Channel');
        $transportSettings = $this->getMockForAbstractClass('Oro\Bundle\IntegrationBundle\Entity\Transport');

        $channel->expects($this->any())
            ->method('getTransport')
            ->will($this->returnValue($transportSettings));

        $this->transport = $this->createMock('Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface');
        $this->contextMediator->expects($this->any())
            ->method('getInitializedTransport')
            ->will($this->returnValue($this->transport));

        $this->contextMediator->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue($channel));

        $this->executionContext = $this->createMock('Akeneo\Bundle\BatchBundle\Item\ExecutionContext');
        $this->jobExecution = $this->createMock('Akeneo\Bundle\BatchBundle\Entity\JobExecution');
        $this->jobExecution->expects($this->any())
            ->method('getExecutionContext')
            ->will($this->returnValue($this->executionContext));

        $this->stepExecutionMock->expects($this->once())
            ->method('getJobExecution')
            ->will($this->returnValue($this->jobExecution));
    }

    /**
     * @return ItemReaderInterface|StepExecutionAwareInterface
     */
    abstract protected function getReader();
}
