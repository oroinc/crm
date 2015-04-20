<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\ImportExportBundle\Job\JobResult;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\IntegrationBundle\ImportExport\Job\Executor;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\AbstractSyncProcessor;
use Oro\Bundle\IntegrationBundle\Tests\Unit\Fixture\TestConnector;
use Oro\Bundle\IntegrationBundle\Tests\Unit\Fixture\TestContext;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;

abstract class AbstractSyncProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractSyncProcessor
     */
    protected $processor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ManagerRegistry
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ProcessorRegistry
     */
    protected $processorRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Executor
     */
    protected $jobExecutor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TypesRegistry
     */
    protected $typesRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LoggerStrategy
     */
    protected $logger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityManager
     */
    protected $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ChannelRepository
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

    public function assertProcessCalls()
    {
        $this->logger->expects($this->any())->method('critical')->with($this->equalTo(''));
        $this->logger->expects($this->never())->method('critical');

        $this->processorRegistry->expects($this->any())
            ->method('getProcessorAliasesByEntity')
            ->will($this->returnValue([]));
    }

    /**
     * @param array $expectedConfig
     */
    public function assertExecuteJob(array $expectedConfig = null)
    {
        $jobResult = new JobResult();
        $jobResult->setContext(new TestContext());
        $jobResult->setSuccessful(true);

        if ($expectedConfig) {
            $this->jobExecutor->expects($this->any())
                ->method('executeJob')
                ->with(
                    // load initial
                    $this->equalTo(ProcessorRegistry::TYPE_IMPORT),
                    $this->equalTo('test job'),
                    $this->callback(
                        function (array $config) use ($expectedConfig) {
                            // dictionary
                            if (!array_key_exists('initialSyncInterval', $config)) {
                                return true;
                            }

                            $this->assertArrayHasKey(ProcessorRegistry::TYPE_IMPORT, $config);

                            $diff = array_diff_key($config[ProcessorRegistry::TYPE_IMPORT], $expectedConfig);
                            if (!$diff) {
                                $this->assertEquals($expectedConfig, $config[ProcessorRegistry::TYPE_IMPORT]);

                                return true;
                            }

                            $intersect = array_diff_key($config[ProcessorRegistry::TYPE_IMPORT], $diff);
                            $this->assertEquals($expectedConfig, $intersect);

                            if ($diff) {
                                $this->assertArrayHasKey('initialSyncedTo', $diff);

                                /** @var \DateTime $date */
                                $date = $diff['initialSyncedTo'];
                                $interval = $date->diff(new \DateTime('now', new \DateTimeZone('UTC')));
                                $this->assertEmpty($interval->m);
                            }

                            return true;
                        }
                    )
                )
                ->will($this->returnValue($jobResult));
        } else {
            $this->jobExecutor->expects($this->any())
                ->method('executeJob')
                ->with(
                    ProcessorRegistry::TYPE_IMPORT,
                    'test job',
                    $this->isType('array')
                )
                ->will($this->returnValue($jobResult));
        }
    }

    /**
     * @param array $connectors
     * @param \DateTime $syncStartDate
     * @param object|null $realConnector
     * @return Channel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getIntegration(array $connectors = [], \DateTime $syncStartDate = null, $realConnector = null)
    {
        $integration = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();

        $integration
            ->expects($this->any())
            ->method('getConnectors')
            ->will($this->returnValue($connectors));

        $integration
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('testChannel'));

        $integration
            ->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('testChannelType'));

        $transport = new MagentoSoapTransport();
        if ($syncStartDate) {
            $transport->setSyncStartDate($syncStartDate);
        }

        $integration
            ->expects($this->any())
            ->method('getTransport')
            ->will($this->returnValue($transport));

        $integration
            ->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue(true));

        if (!$realConnector) {
            $realConnector = new TestConnector();
        }
        $this->typesRegistry
            ->expects($this->any())
            ->method('getConnectorType')
            ->will($this->returnValue($realConnector));

        return $integration;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $integration
     * @param string $connector
     * @param null|object $status
     */
    protected function assertConnectorStatusCall($integration, $connector, $status = null)
    {
        $this->repository->expects($this->atLeastOnce())
            ->method('getLastStatusForConnector')
            ->with($integration, $connector)
            ->will($this->returnValue($status));
    }
}
