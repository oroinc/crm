<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use Symfony\Component\HttpKernel\Log\NullLogger;

use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\ImportExportBundle\Context\Context;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;

use OroCRM\Bundle\MagentoBundle\Provider\AbstractMagentoConnector;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\UpdatedLoaderInterface;

abstract class MagentoConnectorTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var MagentoTransportInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $transportMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $stepExecutionMock;

    public function setUp()
    {
        $this->transportMock     = $this
            ->getMock('OroCRM\\Bundle\\MagentoBundle\\Provider\\Transport\\MagentoTransportInterface');
        $this->stepExecutionMock = $this->getMockBuilder('Akeneo\\Bundle\\BatchBundle\\Entity\\StepExecution')
            ->setMethods(['getExecutionContext'])
            ->disableOriginalConstructor()->getMock();
    }

    public function tearDown()
    {
        unset($this->transportMock, $this->stepExecutionMock);
    }

    public function testInitialization()
    {
        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock);
        $this->transportMock->expects($this->once())->method('init');

        $this->transportMock->expects($this->at(1))->method($this->getIteratorGetterMethodName())
            ->will($this->returnValue($this->getMock('\Iterator')));
        $connector->setStepExecution($this->stepExecutionMock);
    }

    public function testInitializationInUpdatedMode()
    {
        $channel   = new Channel();
        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock, $channel);

        $status = new Status();
        $status->setCode($status::STATUS_COMPLETED);
        $status->setConnector($connector->getType());
        $channel->addStatus($status);

        $this->transportMock->expects($this->once())->method('init');

        $iterator = $this->getMock('OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\UpdatedLoaderInterface');
        $iterator->expects($this->once())->method('setMode')
            ->with($this->equalTo(UpdatedLoaderInterface::IMPORT_MODE_UPDATE));
        $iterator->expects($this->once())->method('setStartDate')->with($this->equalTo($status->getDate()));
        $this->transportMock->expects($this->at(1))->method($this->getIteratorGetterMethodName())
            ->will($this->returnValue($iterator));

        $connector->setStepExecution($this->stepExecutionMock);
    }

    public function testInitializationInForceMode()
    {
        $channel   = new Channel();
        $context   = new Context(['force' => true]);
        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock, $channel, $context);

        $status = new Status();
        $status->setCode($status::STATUS_COMPLETED);
        $status->setConnector($connector->getType());
        $channel->addStatus($status);

        $this->transportMock->expects($this->once())->method('init');

        $iterator = $this->getMock('OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\UpdatedLoaderInterface');
        $iterator->expects($this->exactly((int)!$this->supportsForceMode()))->method('setMode');
        $iterator->expects($this->exactly((int)!$this->supportsForceMode()))->method('setStartDate');
        $this->transportMock->expects($this->at(1))->method($this->getIteratorGetterMethodName())
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
            $this->setExpectedException($exceptionExpected);
        } else {
            $iterator->expects($this->once())->method('setPredefinedFiltersBag');
        }
        $context = new Context(['filters' => []]);

        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock, null, $context);
        $this->transportMock->expects($this->once())->method('init');

        $this->transportMock->expects($this->at(1))->method($this->getIteratorGetterMethodName())
            ->will($this->returnValue($iterator));

        $connector->setStepExecution($this->stepExecutionMock);
    }

    /**
     * @return array
     */
    public function predefinedIteratorProvider()
    {
        $iterator1 = $this->getMock('\Iterator');
        $iterator2 = $this->getMock('OroCRM\Bundle\MagentoBundle\Tests\Unit\Fixtures\PredefinedFiltersAwareFixture');

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

    /**
     * @expectedException \LogicException
     */
    public function testInitializationErrors()
    {
        $connector = $this->getConnector(null, $this->stepExecutionMock);
        $this->transportMock->expects($this->never())->method('init');

        $connector->setStepExecution($this->stepExecutionMock, $this->stepExecutionMock);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Option "transport" should implement "MagentoTransportInterface"
     */
    public function testInitializationErrorsBadTransportGiven()
    {
        $badTransport = $this->getMock('Oro\\Bundle\\IntegrationBundle\\Provider\\TransportInterface');
        $connector    = $this->getConnector($badTransport, $this->stepExecutionMock);
        $this->transportMock->expects($this->never())->method('init');

        $connector->setStepExecution($this->stepExecutionMock);
    }

    public function testRead()
    {
        $iteratorMock = $this->getMock('\Iterator');

        $connector = $this->getConnector($this->transportMock, $this->stepExecutionMock);

        $this->transportMock->expects($this->once())->method('init');

        $this->transportMock->expects($this->at(1))->method($this->getIteratorGetterMethodName())
            ->will($this->returnValue($iteratorMock));
        $connector->setStepExecution($this->stepExecutionMock);

        $testValue = 'test';

        $iteratorMock->expects($this->once())->method('rewind');
        $iteratorMock->expects($this->once())->method('next');
        $iteratorMock->expects($this->exactly(2))->method('valid')->will($this->onConsecutiveCalls(true, false));
        $iteratorMock->expects($this->once())->method('current')->will($this->returnValue($testValue));

        $this->assertEquals($testValue, $connector->read());
        $this->assertNull($connector->read());
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
        $contextRegistryMock = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextRegistry');
        $contextMediatorMock = $this
            ->getMockBuilder('Oro\\Bundle\\IntegrationBundle\\Provider\\ConnectorContextMediator')
            ->disableOriginalConstructor()->getMock();

        $transportSettings = $this->getMockForAbstractClass('Oro\\Bundle\\IntegrationBundle\\Entity\\Transport');
        $channel           = $channel ? : new Channel();
        $channel->setTransport($transportSettings);

        $contextMock = $context ? : new Context([]);

        $executionContext = new ExecutionContext();
        $stepExecutionMock->expects($this->any())
            ->method('getExecutionContext')->will($this->returnValue($executionContext));

        $contextRegistryMock->expects($this->any())->method('getByStepExecution')
            ->will($this->returnValue($contextMock));
        $contextMediatorMock->expects($this->once())
            ->method('getTransport')->with($this->equalTo($contextMock))
            ->will($this->returnValue($transport));
        $contextMediatorMock->expects($this->once())
            ->method('getChannel')->with($this->equalTo($contextMock))
            ->will($this->returnValue($channel));

        $logger = new LoggerStrategy(new NullLogger());

        return $this->getConnectorInstance($contextRegistryMock, $logger, $contextMediatorMock);
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
