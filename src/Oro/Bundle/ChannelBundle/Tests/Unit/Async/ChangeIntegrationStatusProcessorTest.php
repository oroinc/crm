<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Async;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Async\ChangeIntegrationStatusProcessor;
use Oro\Bundle\ChannelBundle\Async\Topic\ChannelStatusChangedTopic;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\TestFrameworkBundle\Test\Logger\LoggerAwareTraitTestTrait;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\Testing\ClassExtensionTrait;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ChangeIntegrationStatusProcessorTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;
    use LoggerAwareTraitTestTrait;

    private StateProvider|\PHPUnit\Framework\MockObject\MockObject $stateProvider;

    private EntityManager|\PHPUnit\Framework\MockObject\MockObject $entityManager;

    private SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session;

    private Channel $channel;

    private ChangeIntegrationStatusProcessor $processor;

    protected function setUp(): void
    {
        $this->stateProvider = $this->createMock(StateProvider::class);
        $this->entityManager = $this->createMock(EntityManager::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->channel = new Channel();

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects(self::any())
            ->method('getManagerForClass')
            ->with(Channel::class)
            ->willReturn($this->entityManager);

        $this->entityManager->expects(self::any())
            ->method('find')
            ->willReturnMap([[Channel::class, 1, null, null, $this->channel]]);

        $this->processor = new ChangeIntegrationStatusProcessor($doctrine, $this->stateProvider);
        $this->setUpLoggerMock($this->processor);
    }


    public function testShouldImplementMessageProcessorInterface(): void
    {
        $this->assertClassImplements(MessageProcessorInterface::class, ChangeIntegrationStatusProcessor::class);
    }

    public function testShouldImplementTopicSubscriberInterface(): void
    {
        $this->assertClassImplements(TopicSubscriberInterface::class, ChangeIntegrationStatusProcessor::class);
    }

    public function testShouldSubscribeOnChannelStatusChangedTopic(): void
    {
        self::assertEquals(
            [ChannelStatusChangedTopic::getName()],
            ChangeIntegrationStatusProcessor::getSubscribedTopics()
        );
    }

    public function testShouldRejectMessageIfChannelNotExist(): void
    {
        $message = new Message();
        $message->setBody(['channelId' => PHP_INT_MAX]);

        $this->loggerMock->expects(self::once())
            ->method('critical')
            ->with(sprintf('Channel not found: %d', PHP_INT_MAX));

        $status = $this->processor->process($message, $this->session);

        self::assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldDoNothingIfChannelDataSourceIsNotInstanceOfIntegration(): void
    {
        $message = new Message();
        $message->setBody(['channelId' => 1]);

        $this->stateProvider->expects(self::once())
            ->method('processChannelChange');

        $status = $this->processor->process($message, $this->session);

        self::assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldSaveChangedIntegration(): void
    {
        $integration = new Integration();

        $this->channel->setDataSource($integration);

        $this->entityManager->expects(self::once())
            ->method('persist')
            ->with(self::identicalTo($integration));
        $this->entityManager->expects(self::once())
            ->method('flush');

        $message = new Message();
        $message->setBody(['channelId' => 1]);

        $this->stateProvider->expects(self::once())
            ->method('processChannelChange');

        $status = $this->processor->process($message, $this->session);

        self::assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldActivateIntegrationWhenChannelActivatedAndPreviousEnableNotDefined(): void
    {
        $integration = new Integration();
        $integration->setEnabled(false);
        $integration->setPreviouslyEnabled(null);
        $integration->setEditMode(Integration::EDIT_MODE_DISALLOW);

        $this->channel->setStatus(true);
        $this->channel->setDataSource($integration);

        $this->stateProvider->expects(self::once())
            ->method('processChannelChange');

        $message = new Message();
        $message->setBody(['channelId' => 1]);

        $status = $this->processor->process($message, $this->session);

        self::assertEquals(MessageProcessorInterface::ACK, $status);

        self::assertTrue($integration->isEnabled());
        self::assertEquals(Integration::EDIT_MODE_RESTRICTED, $integration->getEditMode());
    }

    public function testShouldSetPreviousEnableIntegrationWhenChannelActivatedAndPreviousEnableDefined(): void
    {
        $integration = new Integration();
        $integration->setEnabled(false);
        $integration->setPreviouslyEnabled(false);
        $integration->setEditMode(Integration::EDIT_MODE_DISALLOW);

        $this->channel->setStatus(true);
        $this->channel->setDataSource($integration);

        $this->stateProvider->expects(self::once())
            ->method('processChannelChange');

        $message = new Message();
        $message->setBody(['channelId' => 1]);

        $status = $this->processor->process($message, $this->session);

        self::assertEquals(MessageProcessorInterface::ACK, $status);

        self::assertFalse($integration->isEnabled());
        self::assertFalse($integration->getPreviouslyEnabled());
        self::assertEquals(Integration::EDIT_MODE_RESTRICTED, $integration->getEditMode());
    }

    public function testShouldDeactivateIntegrationWhenChannelDeactivated(): void
    {
        $integration = new Integration();
        $integration->setEnabled(true);
        $integration->setEditMode(Integration::EDIT_MODE_ALLOW);

        $this->channel->setStatus(false);
        $this->channel->setDataSource($integration);

        $this->stateProvider->expects(self::once())
            ->method('processChannelChange');

        $message = new Message();
        $message->setBody(['channelId' => 1]);

        $status = $this->processor->process($message, $this->session);

        self::assertEquals(MessageProcessorInterface::ACK, $status);

        self::assertFalse($integration->isEnabled());
        self::assertEquals(Integration::EDIT_MODE_DISALLOW, $integration->getEditMode());
    }

    public function testShouldUpdatePreviouslyEnabledWhenChannelDeactivated(): void
    {
        $integration = new Integration();
        $integration->setEnabled(true);
        $integration->setPreviouslyEnabled(null);
        $integration->setEditMode(Integration::EDIT_MODE_ALLOW);

        $this->channel->setStatus(false);
        $this->channel->setDataSource($integration);

        $this->stateProvider->expects(self::once())
            ->method('processChannelChange');

        $message = new Message();
        $message->setBody(['channelId' => 1]);

        $status = $this->processor->process($message, $this->session);

        self::assertEquals(MessageProcessorInterface::ACK, $status);

        self::assertTrue($integration->getPreviouslyEnabled());
    }

    public function testShouldNotUpdateEditModeIfIntegrationHasDiffEditMode(): void
    {
        $integration = new Integration();
        $integration->setEnabled(true);
        $integration->setPreviouslyEnabled(null);
        $integration->setEditMode(0);

        $this->channel->setStatus(false);
        $this->channel->setDataSource($integration);

        $this->stateProvider->expects(self::once())
            ->method('processChannelChange');

        $message = new Message();
        $message->setBody(['channelId' => 1]);

        $status = $this->processor->process($message, $this->session);

        self::assertEquals(MessageProcessorInterface::ACK, $status);

        self::assertSame(0, $integration->getEditMode());
    }
}
