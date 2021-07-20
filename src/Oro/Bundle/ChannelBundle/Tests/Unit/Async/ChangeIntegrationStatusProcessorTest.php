<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Async;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Async\ChangeIntegrationStatusProcessor;
use Oro\Bundle\ChannelBundle\Async\Topics;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\Testing\ClassExtensionTrait;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ChangeIntegrationStatusProcessorTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProcessorInterface()
    {
        $this->assertClassImplements(MessageProcessorInterface::class, ChangeIntegrationStatusProcessor::class);
    }

    public function testShouldImplementTopicSubscriberInterface()
    {
        $this->assertClassImplements(TopicSubscriberInterface::class, ChangeIntegrationStatusProcessor::class);
    }

    public function testShouldSubscribeOnChannelStatusChangedTopic()
    {
        $this->assertEquals([Topics::CHANNEL_STATUS_CHANGED], ChangeIntegrationStatusProcessor::getSubscribedTopics());
    }

    public function testCouldBeConstructedWithStateProviderAsFirstArgument()
    {
        new ChangeIntegrationStatusProcessor($this->createRegistryStub(), $this->createLoggerMock());
    }

    public function testShouldLogAndRejectIfMessageBodyMissChangeId()
    {
        $message = new Message();
        $message->setBody('[]');

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('The message invalid. It must have channelId set')
        ;

        $processor = new ChangeIntegrationStatusProcessor($this->createRegistryStub(), $logger);

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testThrowIfMessageBodyInvalidJson()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The malformed json given.');

        $processor = new ChangeIntegrationStatusProcessor($this->createRegistryStub(), $this->createLoggerMock());

        $message = new Message();
        $message->setBody('[}');

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $processor->process($message, $session);
    }

    public function testShouldRejectMessageIfChannelNotExist()
    {
        $entityManagerMock = $this->createEntityManagerMock();
        $entityManagerMock
            ->expects($this->once())
            ->method('find')
            ->with(Channel::class, 'theChannelId')
            ->willReturn(null);
        ;

        $message = new Message();
        $message->setBody(JSON::encode(['channelId' => 'theChannelId']));

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('Channel not found: theChannelId')
        ;

        $registryStub = $this->createRegistryStub($entityManagerMock);

        $processor = new ChangeIntegrationStatusProcessor($registryStub, $logger);

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldDoNothingIfChannelDataSourceIsNotInstanceOfIntegration()
    {
        $channel = new Channel();
        $channel->setDataSource(null);

        $entityManagerMock = $this->createEntityManagerMock();
        $entityManagerMock
            ->expects($this->once())
            ->method('find')
            ->with(Channel::class, 'theChannelId')
            ->willReturn($channel)
        ;

        $registryStub = $this->createRegistryStub($entityManagerMock);

        $processor = new ChangeIntegrationStatusProcessor($registryStub, $this->createLoggerMock());

        $message = new Message();
        $message->setBody(JSON::encode(['channelId' => 'theChannelId']));

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldSaveChangedIntegration()
    {
        $integration = new Integration();

        $channel = new Channel();
        $channel->setDataSource($integration);

        $entityManagerMock = $this->createEntityManagerMock();
        $entityManagerMock
            ->expects($this->once())
            ->method('find')
            ->willReturn($channel)
        ;
        $entityManagerMock
            ->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($integration))
        ;
        $entityManagerMock
            ->expects($this->once())
            ->method('flush')
        ;

        $registryStub = $this->createRegistryStub($entityManagerMock);

        $processor = new ChangeIntegrationStatusProcessor($registryStub, $this->createLoggerMock());

        $message = new Message();
        $message->setBody(JSON::encode(['channelId' => 'aChannelId']));

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldActivateIntegrationWhenChannelActivatedAndPreviousEnableNotDefined()
    {
        $integration = new Integration();
        $integration->setEnabled(false);
        $integration->setPreviouslyEnabled(null);
        $integration->setEditMode(Integration::EDIT_MODE_DISALLOW);

        $channel = new Channel();
        $channel->setStatus(true);
        $channel->setDataSource($integration);

        $entityManagerMock = $this->createEntityManagerMock();
        $entityManagerMock
            ->expects($this->once())
            ->method('find')
            ->willReturn($channel)
        ;

        $registryStub = $this->createRegistryStub($entityManagerMock);

        $processor = new ChangeIntegrationStatusProcessor($registryStub, $this->createLoggerMock());

        $message = new Message();
        $message->setBody(JSON::encode(['channelId' => 'aChannelId']));

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);

        $this->assertTrue($integration->isEnabled());
        $this->assertEquals(Integration::EDIT_MODE_RESTRICTED, $integration->getEditMode());
    }

    public function testShouldSetPreviousEnableIntegrationWhenChannelActivatedAndPreviousEnableDefined()
    {
        $integration = new Integration();
        $integration->setEnabled(false);
        $integration->setPreviouslyEnabled(false);
        $integration->setEditMode(Integration::EDIT_MODE_DISALLOW);

        $channel = new Channel();
        $channel->setStatus(true);
        $channel->setDataSource($integration);

        $entityManagerMock = $this->createEntityManagerMock();
        $entityManagerMock
            ->expects($this->once())
            ->method('find')
            ->willReturn($channel)
        ;

        $registryStub = $this->createRegistryStub($entityManagerMock);

        $processor = new ChangeIntegrationStatusProcessor($registryStub, $this->createLoggerMock());

        $message = new Message();
        $message->setBody(JSON::encode(['channelId' => 'aChannelId']));

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);

        $this->assertFalse($integration->isEnabled());
        $this->assertFalse($integration->getPreviouslyEnabled());
        $this->assertEquals(Integration::EDIT_MODE_RESTRICTED, $integration->getEditMode());
    }

    public function testShouldDeactivateIntegrationWhenChannelDeactivated()
    {
        $integration = new Integration();
        $integration->setEnabled(true);
        $integration->setEditMode(Integration::EDIT_MODE_ALLOW);

        $channel = new Channel();
        $channel->setStatus(false);
        $channel->setDataSource($integration);

        $entityManagerMock = $this->createEntityManagerMock();
        $entityManagerMock
            ->expects($this->once())
            ->method('find')
            ->willReturn($channel)
        ;

        $registryStub = $this->createRegistryStub($entityManagerMock);

        $processor = new ChangeIntegrationStatusProcessor($registryStub, $this->createLoggerMock());

        $message = new Message();
        $message->setBody(JSON::encode(['channelId' => 'aChannelId']));

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);

        $this->assertFalse($integration->isEnabled());
        $this->assertEquals(Integration::EDIT_MODE_DISALLOW, $integration->getEditMode());
    }

    public function testShouldUpdatePreviouslyEnabledWhenChannelDeactivated()
    {
        $integration = new Integration();
        $integration->setEnabled(true);
        $integration->setPreviouslyEnabled(null);
        $integration->setEditMode(Integration::EDIT_MODE_ALLOW);

        $channel = new Channel();
        $channel->setStatus(false);
        $channel->setDataSource($integration);

        $entityManagerMock = $this->createEntityManagerMock();
        $entityManagerMock
            ->expects($this->once())
            ->method('find')
            ->willReturn($channel)
        ;

        $registryStub = $this->createRegistryStub($entityManagerMock);

        $processor = new ChangeIntegrationStatusProcessor($registryStub, $this->createLoggerMock());

        $message = new Message();
        $message->setBody(JSON::encode(['channelId' => 'aChannelId']));

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);

        $this->assertTrue($integration->getPreviouslyEnabled());
    }

    public function testShouldNotUpdateEditModeIfIntegrationHasDiffEditMode()
    {
        $integration = new Integration();
        $integration->setEnabled(true);
        $integration->setPreviouslyEnabled(null);
        $integration->setEditMode(0);

        $channel = new Channel();
        $channel->setStatus(false);
        $channel->setDataSource($integration);

        $entityManagerMock = $this->createEntityManagerMock();
        $entityManagerMock
            ->expects($this->once())
            ->method('find')
            ->willReturn($channel)
        ;

        $registryStub = $this->createRegistryStub($entityManagerMock);

        $processor = new ChangeIntegrationStatusProcessor($registryStub, $this->createLoggerMock());

        $message = new Message();
        $message->setBody(JSON::encode(['channelId' => 'aChannelId']));

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);

        $this->assertSame(0, $integration->getEditMode());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|EntityManagerInterface
     */
    private function createEntityManagerMock()
    {
        return $this->createMock(EntityManagerInterface::class);
    }

    /**
     * @param ObjectManager $entityManager
     * @return \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry
     */
    private function createRegistryStub($entityManager = null)
    {
        $registryMock = $this->createMock(ManagerRegistry::class);
        $registryMock
            ->expects($this->any())
            ->method('getManager')
            ->willReturn($entityManager)
        ;

        return $registryMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject | LoggerInterface
     */
    private function createLoggerMock()
    {
        return $this->createMock(LoggerInterface::class);
    }
}
