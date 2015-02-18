<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\DataGridBundle\Common\Object;
use Oro\Bundle\ImportExportBundle\Job\JobResult;
use Oro\Bundle\IntegrationBundle\Tests\Unit\Fixture\TestConnector;
use Oro\Bundle\IntegrationBundle\Tests\Unit\Fixture\TestContext;
use OroCRM\Bundle\MagentoBundle\Provider\AbstractInitialProcessor;
use OroCRM\Bundle\MagentoBundle\Provider\InitialSyncProcessor;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;

class InitialSyncProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InitialSyncProcessor
     */
    protected $processor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $processorRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $jobExecutor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typesRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repository;

    protected function setUp()
    {
        $this->registry = $this->getMockForAbstractClass('Doctrine\Common\Persistence\ManagerRegistry');
        $this->processorRegistry = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->jobExecutor = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\ImportExport\Job\Executor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->typesRegistry = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Manager\TypesRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->getMockForAbstractClass();
        $this->logger = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->repository = $this
            ->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->em));
        $this->registry->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));

        $this->processor = new InitialSyncProcessor(
            $this->registry,
            $this->processorRegistry,
            $this->jobExecutor,
            $this->typesRegistry,
            $this->eventDispatcher,
            $this->logger
        );
    }

    protected function tearDown()
    {
        unset(
            $this->em,
            $this->repository,
            $this->registry,
            $this->processorRegistry,
            $this->jobExecutor,
            $this->typesRegistry,
            $this->eventDispatcher,
            $this->logger,
            $this->processor
        );
    }

    public function testProcess()
    {
        $this->logger->expects($this->never())
            ->method('critical');

        $this->processorRegistry->expects($this->once())
            ->method('getProcessorAliasesByEntity')
            ->will($this->returnValue([]));

        $connector  = 'testConnector';
        $connectors = [$connector];
        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));
        $syncedTo = new \DateTime('2011-01-02 12:13:14', new \DateTimeZone('UTC'));
        $initialStartDate = new \DateTime('2011-01-03 12:13:14', new \DateTimeZone('UTC'));

        $integration = $this->getIntegration($syncStartDate, $connectors);

        $status = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $status->expects($this->atLeastOnce())
            ->method('getData')
            ->will(
                $this->returnValue(
                    [
                        InitialSyncProcessor::INITIAL_SYNCED_TO => $syncedTo->format(\DateTime::ISO8601)
                    ]
                )
            );
        $this->repository->expects($this->atLeastOnce())
            ->method('getLastStatusForConnector')
            ->with($integration, $connector)
            ->will($this->returnValue($status));

        $syncSettings = Object::create([
            AbstractInitialProcessor::INITIAL_SYNC_START_DATE => $initialStartDate->format(\DateTime::ISO8601)
        ]);
        $integration->expects($this->any())
            ->method('getSynchronizationSettings')
            ->will($this->returnValue($syncSettings));

        $jobResult = new JobResult();
        $jobResult->setContext(new TestContext());
        $jobResult->setSuccessful(true);
        $this->jobExecutor->expects($this->once())
            ->method('executeJob')
            ->with(
                ProcessorRegistry::TYPE_IMPORT,
                'test job',
                [
                    ProcessorRegistry::TYPE_IMPORT => [
                        'processorAlias' => false,
                        'entityName'     => 'testEntity',
                        'channel'        => 'testChannel',
                        'channelType'    => 'testChannelType',
                        AbstractInitialProcessor::INITIAL_SYNCED_TO => $syncedTo,
                        'start_sync_date' => $syncStartDate
                    ]
                ]
            )
            ->will($this->returnValue($jobResult));



        $this->processor->process($integration);
    }

    public function testProcessFirst()
    {
        $this->logger->expects($this->never())
            ->method('critical');

        $this->processorRegistry->expects($this->once())
            ->method('getProcessorAliasesByEntity')
            ->will($this->returnValue([]));

        $connector  = 'testConnector';
        $connectors = [$connector];
        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));
        $initialStartDate = new \DateTime('2011-01-03 12:13:14', new \DateTimeZone('UTC'));

        $integration = $this->getIntegration($syncStartDate, $connectors);

        $this->repository->expects($this->atLeastOnce())
            ->method('getLastStatusForConnector')
            ->with($integration, $connector)
            ->will($this->returnValue(null));

        $syncSettings = Object::create([
            AbstractInitialProcessor::INITIAL_SYNC_START_DATE => $initialStartDate->format(\DateTime::ISO8601)
        ]);
        $integration->expects($this->any())
            ->method('getSynchronizationSettings')
            ->will($this->returnValue($syncSettings));

        $jobResult = new JobResult();
        $jobResult->setContext(new TestContext());
        $jobResult->setSuccessful(true);
        $this->jobExecutor->expects($this->once())
            ->method('executeJob')
            ->with(
                ProcessorRegistry::TYPE_IMPORT,
                'test job',
                [
                    ProcessorRegistry::TYPE_IMPORT => [
                        'processorAlias' => false,
                        'entityName'     => 'testEntity',
                        'channel'        => 'testChannel',
                        'channelType'    => 'testChannelType',
                        AbstractInitialProcessor::INITIAL_SYNCED_TO => $initialStartDate,
                        'start_sync_date' => $syncStartDate
                    ]
                ]
            )
            ->will($this->returnValue($jobResult));



        $this->processor->process($integration);
    }

    /**
     * @param \DateTime $startSyncDate
     * @param array $connectors
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getIntegration(\DateTime $startSyncDate, array $connectors = [])
    {
        $integration = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();

        $integration
            ->expects($this->once())
            ->method('getConnectors')
            ->will($this->returnValue($connectors));

        $integration
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('testChannel'));

        $integration
            ->expects($this->atLeastOnce())
            ->method('getType')
            ->will($this->returnValue('testChannelType'));

        $settingsBag = new ParameterBag();
        $settingsBag->set('start_sync_date', $startSyncDate);
        $transport = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Transport')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $transport->expects($this->any())
            ->method('getSettingsBag')
            ->will($this->returnValue($settingsBag));
        $integration
            ->expects($this->any())
            ->method('getTransport')
            ->will($this->returnValue($transport));

        $integration
            ->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue(true));

        $realConnector = new TestConnector();
        $this->typesRegistry
            ->expects($this->any())
            ->method('getConnectorType')
            ->will($this->returnValue($realConnector));

        return $integration;
    }
}
