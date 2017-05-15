<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Async;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use Psr\Log\LoggerInterface;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
use Oro\Component\MessageQueue\Transport\Null\NullMessage;
use Oro\Component\MessageQueue\Transport\Null\NullSession;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\Testing\ClassExtensionTrait;

class SyncInitialIntegrationProcessorTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    /** @var SyncInitialIntegrationProcessor */
    private $processor;

    /** @var EntityManager|\PHPUnit_Framework_MockObject_MockObject */
    private $entityManager;

    /** @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $entityRepository;

    /** @var InitialSyncProcessor|\PHPUnit_Framework_MockObject_MockObject */
    private $initialSyncProcessor;

    /** @var OptionalListenerManager|\PHPUnit_Framework_MockObject_MockObject */
    private $optionalListenerManager;

    /** @var JobRunner */
    private $jobRunner;

    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $logger;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
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

        $this->processor = new SyncInitialIntegrationProcessor(
            $doctrine,
            $this->initialSyncProcessor,
            $this->optionalListenerManager,
            $this->createMock(CalculateAnalyticsScheduler::class),
            $this->jobRunner,
            $this->createMock(IndexerInterface::class),
            $this->createMock(TokenStorageInterface::class),
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
        $message = new NullMessage();
        $message->setBody('[]');

        $this->logger
            ->expects($this->once())
            ->method('critical')
            ->with('The message invalid. It must have integrationId set', ['message' => $message]);

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

        $status = $this->processor->process($message, new NullSession());

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The malformed json given.
     */
    public function testThrowIfMessageBodyInvalidJson()
    {
        $message = new NullMessage();
        $message->setBody('[}');

        $this->processor->process($message, new NullSession());
    }

    public function testShouldRejectMessageIfIntegrationNotExist()
    {
        $message = new NullMessage();
        $message->setBody(JSON::encode(['integration_id' => 'theIntegrationId']));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Integration not found: theIntegrationId', ['message' => $message]);

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

        $status = $this->processor->process($message, new NullSession());

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

        $message = new NullMessage();
        $message->setBody(JSON::encode(['integration_id' => 'theIntegrationId']));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Integration is not enabled: theIntegrationId', ['message' => $message]);

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

        $status = $this->processor->process($message, new NullSession());

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

        $message = new NullMessage();
        $message->setBody(JSON::encode([
            'integration_id' => 'theIntegrationId',
            'connector' => 'theConnector',
            'connector_parameters' => ['foo' => 'fooVal'],
        ]));
        $message->setMessageId('theMessageId');

        $this->optionalListenerManager
            ->expects($this->once())
            ->method('disableListener')
            ->with('oro_magento.event_listener.delayed_search_reindex');
        $this->optionalListenerManager
            ->expects($this->once())
            ->method('disableListeners')
            ->with([
                'oro_search.index_listener',
                'oro_entity.event_listener.entity_modify_created_updated_properties_listener',
            ]);
        $this->optionalListenerManager
            ->expects($this->once())
            ->method('enableListener')
            ->with('oro_magento.event_listener.delayed_search_reindex');
        $this->optionalListenerManager
            ->expects($this->once())
            ->method('enableListeners')
            ->with([
                'oro_search.index_listener',
                'oro_entity.event_listener.entity_modify_created_updated_properties_listener',
            ]);

        $result = $this->processor->process($message, new NullSession());

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

        $message = new NullMessage();
        $message->setBody(JSON::encode([
            'integration_id' => 'theIntegrationId',
            'connector' => 'theConnector',
            'connector_parameters' => ['foo' => 'fooVal'],
        ]));
        $message->setMessageId('theMessageId');

        $this->optionalListenerManager
            ->expects($this->once())
            ->method('disableListener')
            ->with('oro_magento.event_listener.delayed_search_reindex');
        $this->optionalListenerManager
            ->expects($this->once())
            ->method('disableListeners')
            ->with([
                'oro_search.index_listener',
                'oro_entity.event_listener.entity_modify_created_updated_properties_listener',
            ]);
        $this->optionalListenerManager
            ->expects($this->once())
            ->method('enableListener')
            ->with('oro_magento.event_listener.delayed_search_reindex');
        $this->optionalListenerManager
            ->expects($this->once())
            ->method('enableListeners')
            ->with([
                'oro_search.index_listener',
                'oro_entity.event_listener.entity_modify_created_updated_properties_listener',
            ]);

        $result = $this->processor->process($message, new NullSession());

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

        $message = new NullMessage();
        $message->setBody(JSON::encode(['integration_id' => 'theIntegrationId']));
        $message->setMessageId('theMessageId');

        $this->optionalListenerManager
            ->expects($this->once())
            ->method('disableListener')
            ->with('oro_magento.event_listener.delayed_search_reindex');
        $this->optionalListenerManager
            ->expects($this->once())
            ->method('disableListeners')
            ->with([
                'oro_search.index_listener',
                'oro_entity.event_listener.entity_modify_created_updated_properties_listener',
            ]);
        $this->optionalListenerManager
            ->expects($this->once())
            ->method('enableListener')
            ->with('oro_magento.event_listener.delayed_search_reindex');
        $this->optionalListenerManager
            ->expects($this->once())
            ->method('enableListeners')
            ->with([
                'oro_search.index_listener',
                'oro_entity.event_listener.entity_modify_created_updated_properties_listener',
            ]);

        $this->processor->process($message, new NullSession());

        $uniqueJobs = $this->jobRunner->getRunUniqueJobs();

        $this->assertCount(1, $uniqueJobs);
        $this->assertEquals('orocrm_magento:sync_initial_integration:theIntegrationId', $uniqueJobs[0]['jobName']);
        $this->assertEquals('theMessageId', $uniqueJobs[0]['ownerId']);
    }
}
