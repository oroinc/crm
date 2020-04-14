<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MagentoBundle\Async\Topics;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Provider\AbstractInitialProcessor;
use Oro\Bundle\MagentoBundle\Provider\Connector\AbstractMagentoConnector;
use Oro\Bundle\MagentoBundle\Provider\InitialScheduleProcessor;
use Oro\Bundle\MessageQueueBundle\Test\Unit\MessageQueueExtension;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;

class InitialScheduleProcessorTest extends AbstractSyncProcessorTest
{
    use MessageQueueExtension;

    /** @var InitialScheduleProcessor */
    protected $processor;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = new InitialScheduleProcessor(
            $this->registry,
            $this->processorRegistry,
            $this->jobExecutor,
            $this->typesRegistry,
            $this->eventDispatcher,
            $this->logger,
            ['sync_settings' => ['import_step_interval' => '2 days']]
        );

        $this->processor->setChannelClassName('Oro\IntegrationBundle\Entity\Channel');
        $this->processor->setDoctrineHelper($this->doctrineHelper);
        $this->processor->setMessageProducer(self::getMessageProducer());
    }

    public function testProcessFirstInitial()
    {
        $connector = 'testConnector_initial';
        $connectors = [$connector];

        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));
        $integration = $this->getIntegration($connectors, $syncStartDate);

        $this->em->expects($this->exactly(1))
            ->method('persist');
        $this->em->expects($this->exactly(1))
            ->method('flush');

        $this->assertReloadEntityCall($integration);
        $this->assertProcessCalls();
        $this->assertExecuteJob();

        $this->processor->process($integration);

        self::assertMessageSent(
            Topics::SYNC_INITIAL_INTEGRATION,
            new Message(
                [
                    'integration_id' => 'testChannel',
                    'connector_parameters' => ['skip-dictionary' => true],
                ],
                MessagePriority::VERY_LOW
            )
        );
    }

    public function testProcessExisting()
    {
        $syncedTo = new \DateTime('2011-01-02 12:13:14', new \DateTimeZone('UTC'));
        $initialStartDate = new \DateTime('2011-01-03 12:13:14', new \DateTimeZone('UTC'));
        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));

        $connector = 'testConnector_initial';
        $connectors = [$connector];
        $integration = $this->getIntegration($connectors, $syncStartDate);
        /** @var MagentoTransport $transport */
        $transport = $integration->getTransport();
        $transport->setInitialSyncStartDate($initialStartDate);

        $status = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $status->expects($this->atLeastOnce())
            ->method('getData')
            ->will(
                $this->returnValue(
                    [
                        AbstractInitialProcessor::SYNCED_TO => $syncedTo->format(\DateTime::ISO8601)
                    ]
                )
            );

        $this->assertConnectorStatusCall($integration, $connector, $status);

        $this->assertReloadEntityCall($integration);
        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName' => 'testEntity',
                'channel' => 'testChannel',
                'channelType' => 'testChannelType',
                AbstractMagentoConnector::LAST_SYNC_KEY => $initialStartDate
            ]
        );

        $this->processor->process($integration);

        self::assertMessageSent(
            Topics::SYNC_INITIAL_INTEGRATION,
            new Message(
                [
                    'integration_id' => 'testChannel',
                    'connector_parameters' => ['skip-dictionary' => true],
                ],
                MessagePriority::VERY_LOW
            )
        );
    }

    public function testProcessJobNotRunning()
    {
        $syncedTo = new \DateTime('2011-01-02 12:13:14', new \DateTimeZone('UTC'));
        $initialStartDate = new \DateTime('2011-01-03 12:13:14', new \DateTimeZone('UTC'));
        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));

        $connector = 'testConnector_initial';
        $connectors = [$connector];
        $integration = $this->getIntegration($connectors, $syncStartDate);
        /** @var MagentoTransport $transport */
        $transport = $integration->getTransport();
        $transport->setInitialSyncStartDate($initialStartDate);

        $status = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $status->expects($this->atLeastOnce())
            ->method('getData')
            ->will(
                $this->returnValue(
                    [
                        AbstractInitialProcessor::SYNCED_TO => $syncedTo->format(\DateTime::ISO8601)
                    ]
                )
            );

        $this->assertConnectorStatusCall($integration, $connector, $status);

        $this->assertReloadEntityCall($integration);
        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName' => 'testEntity',
                'channel' => 'testChannel',
                'channelType' => 'testChannelType',
                AbstractMagentoConnector::LAST_SYNC_KEY => $initialStartDate
            ]
        );

        $this->processor->process($integration);

        self::assertMessageSent(
            Topics::SYNC_INITIAL_INTEGRATION,
            new Message(
                [
                    'integration_id' => 'testChannel',
                    'connector_parameters' => ['skip-dictionary' => true],
                ],
                MessagePriority::VERY_LOW
            )
        );
    }

    public function testProcessInitialSynced()
    {
        $initialStartDate = new \DateTime('2011-01-03 12:13:14', new \DateTimeZone('UTC'));
        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));
        $syncedTo = $syncStartDate->sub(new \DateInterval('P1D'));

        $connector = 'testConnector_initial';
        $connectors = [$connector];
        $integration = $this->getIntegration($connectors, $syncStartDate);

        /** @var MagentoTransport $transport */
        $transport = $integration->getTransport();
        $transport->setInitialSyncStartDate($initialStartDate);

        $status = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $status->expects($this->atLeastOnce())
            ->method('getData')
            ->will(
                $this->returnValue(
                    [
                        AbstractInitialProcessor::SYNCED_TO => $syncedTo->format(\DateTime::ISO8601)
                    ]
                )
            );

        $this->assertConnectorStatusCall($integration, $connector, $status);

        $this->assertReloadEntityCall($integration);
        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName' => 'testEntity',
                'channel' => 'testChannel',
                'channelType' => 'testChannelType',
                AbstractMagentoConnector::LAST_SYNC_KEY => $initialStartDate
            ]
        );

        $this->processor->process($integration);

        self::assertMessagesEmpty(Topics::SYNC_INITIAL_INTEGRATION);
    }

    public function testProcessForce()
    {
        $initialStartDate = new \DateTime('2011-01-03 12:13:14', new \DateTimeZone('UTC'));
        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));
        $syncedTo = $syncStartDate->sub(new \DateInterval('P1D'));

        $connector = 'testConnector_initial';
        $connectors = [$connector];

        $connectorInstance = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Provider\AbstractConnector')
            ->disableOriginalConstructor()
            ->setMethods(['supportsForceSync', 'getImportJobName'])
            ->getMockForAbstractClass();
        $connectorInstance->expects($this->once())
            ->method('supportsForceSync')
            ->will($this->returnValue(true));
        $connectorInstance->expects($this->any())
            ->method('getImportJobName')
            ->will($this->returnValue('test job'));
        $integration = $this->getIntegration($connectors, $syncStartDate, $connectorInstance);

        /** @var MagentoTransport $transport */
        $transport = $integration->getTransport();
        $transport->setInitialSyncStartDate($initialStartDate);

        $status = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $status->expects($this->atLeastOnce())
            ->method('getData')
            ->will(
                $this->returnValue(
                    [
                        AbstractInitialProcessor::SYNCED_TO => $syncedTo->format(\DateTime::ISO8601)
                    ]
                )
            );
        $status->expects($this->once())
            ->method('setData')
            ->with(
                [
                    AbstractInitialProcessor::SYNCED_TO => $syncedTo->format(\DateTime::ISO8601),
                    AbstractInitialProcessor::SKIP_STATUS => true
                ]
            );
        $this->repository->expects($this->once())
            ->method('getConnectorStatuses')
            ->with($integration, $connector)
            ->will($this->returnValue(new \ArrayIterator([$status])));

        $this->assertConnectorStatusCall($integration, $connector, $status);

        $this->assertReloadEntityCall($integration);
        $this->assertProcessCalls();
        $this->assertExecuteJob(
            [
                'processorAlias' => false,
                'entityName' => 'testEntity',
                'channel' => 'testChannel',
                'channelType' => 'testChannelType',
                AbstractMagentoConnector::LAST_SYNC_KEY => $initialStartDate
            ]
        );

        $this->processor->process($integration, null, ['force' => true]);

        self::assertMessagesEmpty(Topics::SYNC_INITIAL_INTEGRATION);
    }

    public function testProcessInitialAfterForce()
    {
        $initialStartDate = new \DateTime('2011-01-03 12:13:14', new \DateTimeZone('UTC'));
        $syncStartDate = new \DateTime('2000-01-01 00:00:00', new \DateTimeZone('UTC'));

        $status = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Status')
            ->disableOriginalConstructor()
            ->getMock();
        $status->expects($this->atLeastOnce())
            ->method('getData')
            ->will(
                $this->returnValue(
                    [
                        AbstractInitialProcessor::SYNCED_TO => $initialStartDate->format(\DateTime::ISO8601),
                        AbstractInitialProcessor::SKIP_STATUS => true
                    ]
                )
            );

        $connector = 'testConnector_initial';
        $connectors = [$connector];
        $integration = $this->getIntegration($connectors, $syncStartDate);

        $this->assertConnectorStatusCall($integration, $connector, $status);

        $this->assertReloadEntityCall($integration);
        $this->assertProcessCalls();
        $this->assertExecuteJob();

        $this->processor->process($integration);
    }

    /**
     * {@inheritdoc}
     */
    protected function getIntegration(array $connectors = [], \DateTime $syncStartDate = null, $realConnector = null)
    {
        $dictionaryConnector = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Provider\Connector\WebsiteConnector')
            ->disableOriginalConstructor()
            ->getMock();
        $dictionaryConnector->expects($this->any())
            ->method('getType')
            ->willReturn('dictionary');
        $dictionaryConnector->expects($this->any())
            ->method('getImportJobName')
            ->willReturn('test job');

        $this->typesRegistry->expects($this->any())
            ->method('getRegisteredConnectorsTypes')
            ->willReturn(new ArrayCollection(['dictionaryConnector' => $dictionaryConnector]));

        return parent::getIntegration($connectors, $syncStartDate, $realConnector);
    }

    /**
     * @param object $entity
     */
    protected function assertReloadEntityCall($entity)
    {
        $class = get_class($entity);
        $id = 1;
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityClass')
            ->with($entity)
            ->will($this->returnValue($class));

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityIdentifier')
            ->will($this->returnValue($id));

        $this->doctrineHelper->expects($this->once())
            ->method('getEntity')
            ->with($class, $id)
            ->will($this->returnValue($entity));
    }
}
