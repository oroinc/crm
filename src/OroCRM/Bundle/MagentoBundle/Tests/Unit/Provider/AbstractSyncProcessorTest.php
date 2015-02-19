<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\ImportExportBundle\Job\JobResult;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\ImportExportBundle\Job\JobExecutorInterface;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\AbstractSyncProcessor;
use Oro\Bundle\IntegrationBundle\Tests\Unit\Fixture\TestConnector;
use Oro\Bundle\IntegrationBundle\Tests\Unit\Fixture\TestContext;
use Oro\Bundle\DataGridBundle\Common\Object;

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
     * @var \PHPUnit_Framework_MockObject_MockObject|JobExecutorInterface
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
        $this->jobExecutor = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Job\JobExecutorInterface')->getMock();
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

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $integration
     * @param array $settings
     * @return Object
     */
    public function assertIntegrationSettingsCall($integration, array $settings = [])
    {
        $syncSettings = Object::create($settings);
        $integration->expects($this->any())
            ->method('getSynchronizationSettings')
            ->will($this->returnValue($syncSettings));

        return $syncSettings;
    }

    public function assertProcessCalls()
    {
        $this->logger->expects($this->never())
            ->method('critical');

        $this->processorRegistry->expects($this->once())
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
            $this->jobExecutor->expects($this->once())
                ->method('executeJob')
                ->with(
                    ProcessorRegistry::TYPE_IMPORT,
                    'test job',
                    [ProcessorRegistry::TYPE_IMPORT => $expectedConfig]
                )
                ->will($this->returnValue($jobResult));
        } else {
            $this->jobExecutor->expects($this->once())
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
     * @param array $settings
     * @return \PHPUnit_Framework_MockObject_MockObject|Channel
     */
    protected function getIntegration(array $connectors = [], array $settings = [])
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

        $settingsBag = new ParameterBag($settings);
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
