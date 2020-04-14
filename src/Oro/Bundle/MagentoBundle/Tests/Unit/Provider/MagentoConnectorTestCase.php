<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\ImportExportBundle\Context\Context;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Oro\Bundle\MagentoBundle\Provider\Connector\AbstractMagentoConnector;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Psr\Log\NullLogger;

abstract class MagentoConnectorTestCase extends \PHPUnit\Framework\TestCase
{
    /** @var MagentoTransportInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $transportMock;

    /** @var StepExecution|\PHPUnit\Framework\MockObject\MockObject */
    protected $stepExecutionMock;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    protected $managerRegistryMock;

    /** @var ChannelRepository|\PHPUnit\Framework\MockObject\MockObject */
    protected $integrationRepositoryMock;

    /** @var JobExecution|\PHPUnit\Framework\MockObject\MockObject */
    protected $jobExecutionMock;

    /** @var ExecutionContext|\PHPUnit\Framework\MockObject\MockObject */
    protected $executionContextMock;

    /** @var array */
    protected $config = [
        'sync_settings' => ['mistiming_assumption_interval' => '2 minutes']
    ];

    protected function setUp(): void
    {
        $this->transportMock     = $this
            ->createMock('Oro\\Bundle\\MagentoBundle\\Provider\\Transport\\MagentoTransportInterface');

        $this->stepExecutionMock = $this->getMockBuilder('Akeneo\\Bundle\\BatchBundle\\Entity\\StepExecution')
            ->setMethods(['getExecutionContext', 'getJobExecution'])
            ->disableOriginalConstructor()->getMock();

        $this->executionContextMock = $this->createMock('Akeneo\Bundle\BatchBundle\Item\ExecutionContext');

        $this->jobExecutionMock = $this->createMock('Akeneo\Bundle\BatchBundle\Entity\JobExecution');
        $this->jobExecutionMock->expects($this->any())
            ->method('getExecutionContext')
            ->will($this->returnValue($this->executionContextMock));

        $this->stepExecutionMock->expects($this->any())
            ->method('getJobExecution')
            ->will($this->returnValue($this->jobExecutionMock));

        $this->managerRegistryMock = $this->getMockBuilder('Doctrine\\Common\\Persistence\\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->integrationRepositoryMock = $this->getMockBuilder(
            'Oro\\Bundle\\IntegrationBundle\\Entity\\Repository\\ChannelRepository'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->managerRegistryMock->expects($this->any())
            ->method('getRepository')
            ->with('OroIntegrationBundle:Channel')
            ->will($this->returnValue($this->integrationRepositoryMock));
    }

    protected function tearDown(): void
    {
        unset($this->transportMock, $this->stepExecutionMock);
    }

    public function testInitialization()
    {
        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock);
        $this->transportMock->expects($this->at(0))->method($this->getIteratorGetterMethodName())
            ->will($this->returnValue($this->createMock('\Iterator')));
        $connector->setStepExecution($this->stepExecutionMock);
    }

    public function testInitializationInUpdatedMode()
    {
        $channel   = new Channel();
        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock, $channel);

        $status = new Status();
        $status->setCode($status::STATUS_COMPLETED);
        $status->setConnector($connector->getType());
        $status->setDate(new \DateTime('-10 days', new \DateTimeZone('UTC')));

        $this->expectLastCompletedStatusForConnector($status, $channel, $connector->getType());

        $expectedDateInFilter = clone $status->getDate();
        $assumptionInterval   = $this->config['sync_settings']['mistiming_assumption_interval'];
        $expectedDateInFilter->sub(\DateInterval::createFromDateString($assumptionInterval));

        $iterator = $this->createMock('Oro\\Bundle\\MagentoBundle\\Provider\\Iterator\\UpdatedLoaderInterface');
        $iterator->expects($this->once())->method('setStartDate')->with($this->equalTo($expectedDateInFilter));
        $this->transportMock->expects($this->at(0))->method($this->getIteratorGetterMethodName())
            ->will($this->returnValue($iterator));

        $connector->setStepExecution($this->stepExecutionMock);
    }

    /**
     * @dataProvider predefinedIteratorProvider
     *
     * @param mixed $iterator
     * @param null  $exceptionExpected
     */
    public function testInitializationWithPredefinedFilters($iterator, $exceptionExpected = null)
    {
        if (null !== $exceptionExpected) {
            $this->expectException($exceptionExpected);
        } else {
            $iterator->expects($this->once())->method('setPredefinedFiltersBag');
        }
        $context = new Context(['filters' => ['test' => 1]]);

        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock, null, $context);

        $this->transportMock->expects($this->at(0))->method($this->getIteratorGetterMethodName())
            ->will($this->returnValue($iterator));

        $connector->setStepExecution($this->stepExecutionMock);
    }

    /**
     * @return array
     */
    public function predefinedIteratorProvider()
    {
        $iterator1 = $this->createMock('\Iterator');
        $iterator2 = $this->createMock('Oro\Bundle\MagentoBundle\Tests\Unit\Fixtures\PredefinedFiltersAwareFixture');

        return [
            'should throw exception' => [
                $iterator1,
                '\LogicException'
            ],
            'should process filters' => [
                $iterator2
            ]
        ];
    }

    public function testInitializationErrors()
    {
        $this->expectException(\LogicException::class);
        $connector = $this->getConnector(null, $this->stepExecutionMock);
        $this->transportMock->expects($this->never())->method('init');

        $connector->setStepExecution($this->stepExecutionMock);
    }

    public function testInitializationErrorsBadTransportGiven()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Option "transport" should implement "MagentoTransportInterface"');

        $badTransport = $this->createMock('Oro\\Bundle\\IntegrationBundle\\Provider\\TransportInterface');
        $connector    = $this->getConnector($badTransport, $this->stepExecutionMock);
        $this->transportMock->expects($this->never())->method('init');

        $connector->setStepExecution($this->stepExecutionMock);
    }

    /**
     * @dataProvider readItemDatesDataProvider
     *
     * @param string  $dateInContext
     * @param string  $dateInItem
     * @param string  $expectedDate
     * @param boolean $hasData
     * @param string  $dateInIterator
     */
    public function testRead($dateInContext, $dateInItem, $expectedDate, $hasData = true, $dateInIterator = null)
    {
        $iteratorMock = $this->createMock('Oro\\Bundle\\MagentoBundle\\Provider\\Iterator\\UpdatedLoaderInterface');

        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock);

        $this->transportMock->expects($this->at(0))->method($this->getIteratorGetterMethodName())
            ->will($this->returnValue($iteratorMock));

        $connector->setStepExecution($this->stepExecutionMock);
        $context = $this->stepExecutionMock->getExecutionContext();
        $context->put(ConnectorInterface::CONTEXT_CONNECTOR_DATA_KEY, ['lastSyncItemDate' => $dateInContext]);

        $testValue = [
            'created_at' => '01.01.2200 14:15:08',
            'updatedAt'  => $dateInItem
        ];

        if ($hasData) {
            $context->put(ConnectorInterface::CONTEXT_CONNECTOR_DATA_KEY, ['lastSyncItemDate' => $dateInContext]);

            $iteratorMock->expects($this->once())->method('rewind');
            $iteratorMock->expects($this->once())->method('next');
            $iteratorMock->expects($this->any())->method('valid')->will($this->onConsecutiveCalls(true, false));
            $iteratorMock->expects($this->once())->method('current')->will($this->returnValue($testValue));

            $this->assertEquals($testValue, $connector->read());
        } else {
            $context->put(ConnectorInterface::CONTEXT_CONNECTOR_DATA_KEY, ['lastSyncItemDate' => $dateInIterator]);

            $iteratorMock->expects($this->once())->method('rewind');
            $iteratorMock->expects($this->never())->method('next');
            $iteratorMock->expects($this->any())->method('valid')->will($this->returnValue(false));
            $iteratorMock->expects($this->never())->method('current')->will($this->returnValue(null));
            $iteratorMock->expects($this->at(0))->method('getStartDate')
                ->will($this->returnValue(new \Datetime($dateInIterator)));
            $iteratorMock->expects($this->at(1))->method('getStartDate')->will($this->returnValue($dateInIterator));
        }

        $this->assertNull($connector->read());

        $connectorData = $context->get(ConnectorInterface::CONTEXT_CONNECTOR_DATA_KEY);

        $this->assertArrayHasKey('lastSyncItemDate', $connectorData);

        if ($hasData) {
            $this->assertSame($expectedDate, $connectorData['lastSyncItemDate']);
        } else {
            $this->assertSame($dateInIterator, $connectorData['lastSyncItemDate']);
        }
    }

    /**
     * @return array
     */
    public function readItemDatesDataProvider()
    {
        return [
            'empty context, should take updated date from item'                 => [
                '$dateInContext' => null,
                '$dateInItem'    => '01.01.2000 14:15:08',
                '$expectedDate'  => '01.01.2000 14:15:08',
            ],
            'date in context given but empty date in item, should not override' => [
                '$dateInContext' => '01.01.2000 14:15:08',
                '$dateInItem'    => null,
                '$expectedDate'  => '01.01.2000 14:15:08',
            ],
            'should take greater from item'                                     => [
                '$dateInContext' => '01.01.2000 14:15:08',
                '$dateInItem'    => '01.02.2000 14:15:08',
                '$expectedDate'  => '01.02.2000 14:15:08',
            ],
            'should take greater from context'                                  => [
                '$dateInContext' => '01.01.2001 14:15:08',
                '$dateInItem'    => '01.01.2000 14:15:08',
                '$expectedDate'  => '01.01.2001 14:15:08',
            ],
            'without data'                                                      => [
                '$dateInContext' => null,
                '$dateInItem'    => null,
                '$expectedDate'  => null,
                false,
                '01.01.2010 14:15:08',
            ],
        ];
    }

    /**
     * @param mixed        $transport
     * @param mixed        $stepExecutionMock
     * @param null|Channel $channel
     *
     * @param null         $context
     *
     * @return AbstractMagentoConnector
     */
    protected function getConnector($transport, $stepExecutionMock, $channel = null, $context = null)
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|ContextRegistry $contextRegistryMock */
        $contextRegistryMock = $this->createMock('Oro\Bundle\ImportExportBundle\Context\ContextRegistry');

        /** @var \PHPUnit\Framework\MockObject\MockObject|ConnectorContextMediator $contextMediatorMock */
        $contextMediatorMock = $this
            ->getMockBuilder('Oro\\Bundle\\IntegrationBundle\\Provider\\ConnectorContextMediator')
            ->disableOriginalConstructor()->getMock();

        /** @var \PHPUnit\Framework\MockObject\MockObject|Transport $transportSettings */
        $transportSettings = $this->getMockForAbstractClass('Oro\\Bundle\\IntegrationBundle\\Entity\\Transport');
        $channel           = $channel ? : new Channel();
        $channel->setTransport($transportSettings);

        $contextMock = $context ? : new Context([]);

        $executionContext = new ExecutionContext();
        $stepExecutionMock->expects($this->any())
            ->method('getExecutionContext')->will($this->returnValue($executionContext));

        $contextRegistryMock->expects($this->any())->method('getByStepExecution')
            ->will($this->returnValue($contextMock));
        $contextMediatorMock->expects($this->any())
            ->method('getInitializedTransport')->with($this->equalTo($channel))
            ->will($this->returnValue($transport));
        $contextMediatorMock->expects($this->any())
            ->method('getChannel')->with($this->equalTo($contextMock))
            ->will($this->returnValue($channel));

        $logger = new LoggerStrategy(new NullLogger());

        $connector = $this->getConnectorInstance($contextRegistryMock, $logger, $contextMediatorMock);
        $connector->setManagerRegistry($this->managerRegistryMock);

        return $connector;
    }

    /**
     * @param Status $expectedStatus
     * @param Channel $channel
     * @param string $connector
     */
    protected function expectLastCompletedStatusForConnector($expectedStatus, $channel, $connector)
    {
        $this->integrationRepositoryMock->expects($this->once())
            ->method('getLastStatusForConnector')
            ->with($channel, $connector, Status::STATUS_COMPLETED)
            ->will($this->returnValue($expectedStatus));
    }

    /**
     * @return bool
     */
    protected function supportsForceMode()
    {
        return false;
    }

    /**
     * @param ContextRegistry          $contextRegistry
     * @param LoggerStrategy           $logger
     * @param ConnectorContextMediator $contextMediator
     *
     * @return AbstractMagentoConnector
     */
    abstract protected function getConnectorInstance(
        ContextRegistry $contextRegistry,
        LoggerStrategy $logger,
        ConnectorContextMediator $contextMediator
    );

    /**
     * @return string
     */
    abstract protected function getIteratorGetterMethodName();
}
