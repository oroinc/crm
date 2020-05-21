<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Async;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Logger\LoggerStrategy;
use Oro\Bundle\MagentoBundle\Async\SyncInitialIntegrationProcessor;
use Oro\Bundle\MagentoBundle\Async\Topics;
use Oro\Bundle\MagentoBundle\Provider\InitialSyncProcessor;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;
use Oro\Bundle\SearchBundle\Engine\IndexerInterface;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Test\JobRunner;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\Testing\ClassExtensionTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SyncInitialIntegrationProcessorTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;

    /** @var SyncInitialIntegrationProcessor */
    private $processor;

    /** @var EntityManager|\PHPUnit\Framework\MockObject\MockObject */
    private $entityManager;

    /** @var EntityRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $entityRepository;

    /** @var InitialSyncProcessor|\PHPUnit\Framework\MockObject\MockObject */
    private $initialSyncProcessor;

    /** @var OptionalListenerManager|\PHPUnit\Framework\MockObject\MockObject */
    private $optionalListenerManager;

    /** @var JobRunner */
    private $jobRunner;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $connectionMock = $this->createMock(Connection::class);
        $connectionMock
            ->expects($this->any())
            ->method('getConfiguration')
            ->willReturn(new Configuration());

        $this->entityManager = $this->createMock(EntityManager::class);
        $this->entityManager
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock);

        $this->entityRepository = $this->createMock(EntityRepository::class);

        /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject $doctrine */
        $doctrine = $this->createMock(DoctrineHelper::class);
        $doctrine
            ->expects($this->any())
            ->method('getEntityManager')
            ->with(Integration::class)
            ->willReturn($this->entityManager);
        $doctrine
            ->expects($this->any())
            ->method('getEntityRepository')
            ->with(Channel::class)
            ->willReturn($this->entityRepository);

        $this->initialSyncProcessor = $this->createMock(InitialSyncProcessor::class);
        $this->initialSyncProcessor
            ->expects($this->any())
            ->method('getLoggerStrategy')
            ->willReturn(new LoggerStrategy());

        $this->optionalListenerManager = $this->createMock(OptionalListenerManager::class);
        $this->jobRunner = new JobRunner();
        $this->logger = $this->createMock(LoggerInterface::class);

        /** @var CalculateAnalyticsScheduler|\PHPUnit\Framework\MockObject\MockObject $scheduler */
        $scheduler = $this->createMock(CalculateAnalyticsScheduler::class);
        /** @var IndexerInterface|\PHPUnit\Framework\MockObject\MockObject $indexer */
        $indexer = $this->createMock(IndexerInterface::class);
        /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject $tokenStorage */
        $tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->processor = new SyncInitialIntegrationProcessor(
            $doctrine,
            $this->initialSyncProcessor,
            $this->optionalListenerManager,
            $scheduler,
            $this->jobRunner,
            $indexer,
            $tokenStorage,
            $this->logger
        );
    }

    public function testShouldImplementMessageProcessorInterface()
    {
        $this->assertClassImplements(MessageProcessorInterface::class, SyncInitialIntegrationProcessor::class);
    }

    public function testShouldImplementTopicSubscriberInterface()
    {
        $this->assertClassImplements(TopicSubscriberInterface::class, SyncInitialIntegrationProcessor::class);
    }

    public function testShouldSubscribeOnSyncInitialIntegrationTopic()
    {
        $this->assertEquals([Topics::SYNC_INITIAL_INTEGRATION], SyncInitialIntegrationProcessor::getSubscribedTopics());
    }

    public function testShouldLogAndRejectIfMessageBodyMissIntegrationId()
    {
        $message = new Message();
        $message->setBody('[]');

        $this->logger
            ->expects($this->once())
            ->method('critical')
            ->with('The message invalid. It must have integrationId set');

        $this->optionalListenerManager
            ->expects($this->never())
            ->method('disableListener');
        $this->optionalListenerManager
            ->expects($this->never())
            ->method('disableListeners');
        $this->optionalListenerManager
            ->expects($this->never())
            ->method('enableListener');
        $this->optionalListenerManager
            ->expects($this->never())
            ->method('enableListeners');

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $this->processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testThrowIfMessageBodyInvalidJson()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The malformed json given.');

        $message = new Message();
        $message->setBody('[}');

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $this->processor->process($message, $session);
    }

    public function testShouldRejectMessageIfIntegrationNotExist()
    {
        $message = new Message();
        $message->setBody(JSON::encode(['integration_id' => 'theIntegrationId']));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Integration not found: theIntegrationId');

        $this->optionalListenerManager
            ->expects($this->never())
            ->method('disableListener');
        $this->optionalListenerManager
            ->expects($this->never())
            ->method('enableListener');

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $this->processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldRejectMessageIfIntegrationIsNotEnabled()
    {
        $integration = new Integration();
        $integration->setEnabled(false);
        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->with(Integration::class)
            ->willReturn($integration);

        $message = new Message();
        $message->setBody(JSON::encode(['integration_id' => 'theIntegrationId']));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Integration is not enabled: theIntegrationId');

        $this->optionalListenerManager
            ->expects($this->never())
            ->method('disableListener');
        $this->optionalListenerManager
            ->expects($this->never())
            ->method('enableListener');

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $this->processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldAckMessageIfInitialSyncProcessorProcessMessageSuccessfully()
    {
        $integration = new Integration();
        $integration->setEnabled(true);
        $integration->setOrganization(new Organization());
        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->with(Integration::class)
            ->willReturn($integration);

        $channel = new Channel();
        $this->entityRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn($channel);

        $this->initialSyncProcessor
            ->expects($this->once())
            ->method('process')
            ->with(
                $this->identicalTo($integration),
                'theConnector',
                ['foo' => 'fooVal']
            )
            ->willReturn(true);

        $message = new Message();
        $message->setBody(JSON::encode([
            'integration_id' => 'theIntegrationId',
            'connector' => 'theConnector',
            'connector_parameters' => ['foo' => 'fooVal'],
        ]));
        $message->setMessageId('theMessageId');

        $this->optionalListenerManager
            ->expects($this->exactly(2))
            ->method('getListeners')
            ->willReturn([
                'oro_search.index_listener',
                'oro_entity.event_listener.entity_modify_created_updated_properties_listener',
                'oro_magento.event_listener.delayed_search_reindex'
            ]);
        $this->optionalListenerManager
            ->expects($this->exactly(3))
            ->method('disableListener')
            ->withConsecutive(
                ['oro_search.index_listener'],
                ['oro_entity.event_listener.entity_modify_created_updated_properties_listener'],
                ['oro_magento.event_listener.delayed_search_reindex']
            );
        $this->optionalListenerManager
            ->expects($this->exactly(3))
            ->method('enableListener')
            ->withConsecutive(
                ['oro_magento.event_listener.delayed_search_reindex'],
                ['oro_search.index_listener'],
                ['oro_entity.event_listener.entity_modify_created_updated_properties_listener']
            );

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $result = $this->processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $result);
    }

    public function testShouldRejectMessageIfInitialSyncProcessorProcessMessageFailed()
    {
        $integration = new Integration();
        $integration->setEnabled(true);
        $integration->setOrganization(new Organization());
        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->with(Integration::class)
            ->willReturn($integration);

        $this->initialSyncProcessor
            ->expects($this->once())
            ->method('process')
            ->with(
                $this->identicalTo($integration),
                'theConnector',
                ['foo' => 'fooVal']
            )
            ->willReturn(false);

        $message = new Message();
        $message->setBody(JSON::encode([
            'integration_id' => 'theIntegrationId',
            'connector' => 'theConnector',
            'connector_parameters' => ['foo' => 'fooVal'],
        ]));
        $message->setMessageId('theMessageId');

        $this->optionalListenerManager
            ->expects($this->exactly(2))
            ->method('getListeners')
            ->willReturn([
                'oro_search.index_listener',
                'oro_entity.event_listener.entity_modify_created_updated_properties_listener',
                'oro_magento.event_listener.delayed_search_reindex'
            ]);
        $this->optionalListenerManager
            ->expects($this->exactly(3))
            ->method('disableListener')
            ->withConsecutive(
                ['oro_search.index_listener'],
                ['oro_entity.event_listener.entity_modify_created_updated_properties_listener'],
                ['oro_magento.event_listener.delayed_search_reindex']
            );
        $this->optionalListenerManager
            ->expects($this->exactly(3))
            ->method('enableListener')
            ->withConsecutive(
                ['oro_magento.event_listener.delayed_search_reindex'],
                ['oro_search.index_listener'],
                ['oro_entity.event_listener.entity_modify_created_updated_properties_listener']
            );

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $result = $this->processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $result);
    }

    public function testShouldRunSyncAsUniqueJob()
    {
        $integration = new Integration();
        $integration->setEnabled(true);
        $integration->setOrganization(new Organization());
        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->with(Integration::class)
            ->willReturn($integration);

        $message = new Message();
        $message->setBody(JSON::encode(['integration_id' => 'theIntegrationId']));
        $message->setMessageId('theMessageId');

        $this->optionalListenerManager
            ->expects($this->exactly(2))
            ->method('getListeners')
            ->willReturn([
                'oro_search.index_listener',
                'oro_entity.event_listener.entity_modify_created_updated_properties_listener',
                'oro_magento.event_listener.delayed_search_reindex'
            ]);
        $this->optionalListenerManager
            ->expects($this->exactly(3))
            ->method('disableListener')
            ->withConsecutive(
                ['oro_search.index_listener'],
                ['oro_entity.event_listener.entity_modify_created_updated_properties_listener'],
                ['oro_magento.event_listener.delayed_search_reindex']
            );
        $this->optionalListenerManager
            ->expects($this->exactly(3))
            ->method('enableListener')
            ->withConsecutive(
                ['oro_magento.event_listener.delayed_search_reindex'],
                ['oro_search.index_listener'],
                ['oro_entity.event_listener.entity_modify_created_updated_properties_listener']
            );

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $this->processor->process($message, $session);

        $uniqueJobs = $this->jobRunner->getRunUniqueJobs();

        $this->assertCount(1, $uniqueJobs);
        $this->assertEquals('orocrm_magento:sync_initial_integration:theIntegrationId', $uniqueJobs[0]['jobName']);
        $this->assertEquals('theMessageId', $uniqueJobs[0]['ownerId']);
    }
}
