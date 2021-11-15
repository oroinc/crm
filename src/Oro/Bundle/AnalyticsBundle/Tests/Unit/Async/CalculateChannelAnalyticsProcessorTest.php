<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Async;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\AnalyticsBundle\Async\CalculateChannelAnalyticsProcessor;
use Oro\Bundle\AnalyticsBundle\Async\Topics;
use Oro\Bundle\AnalyticsBundle\Builder\AnalyticsBuilder;
use Oro\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface;
use Oro\Bundle\AnalyticsBundle\Tests\Unit\Model\Stub\CustomerAwareStub;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Test\JobRunner;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\Testing\ClassExtensionTrait;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CalculateChannelAnalyticsProcessorTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProcessorInterface()
    {
        $this->assertClassImplements(MessageProcessorInterface::class, CalculateChannelAnalyticsProcessor::class);
    }

    public function testShouldImplementTopicSubscriberInterface()
    {
        $this->assertClassImplements(TopicSubscriberInterface::class, CalculateChannelAnalyticsProcessor::class);
    }

    public function testShouldSubscribeOnCalculateChannelAnalyticsTopic()
    {
        $this->assertEquals(
            [Topics::CALCULATE_CHANNEL_ANALYTICS],
            CalculateChannelAnalyticsProcessor::getSubscribedTopics()
        );
    }

    public function testCouldBeConstructedWithExpectedArguments()
    {
        new CalculateChannelAnalyticsProcessor(
            $this->getDoctrineHelper(),
            $this->createMock(AnalyticsBuilder::class),
            new JobRunner(),
            $this->createMock(LoggerInterface::class)
        );
    }

    public function testShouldLogAndRejectIfMessageBodyMissChannelId()
    {
        $message = new Message();
        $message->setBody('[]');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('critical')
            ->with('The message invalid. It must have channel_id set');

        $processor = new CalculateChannelAnalyticsProcessor(
            $this->getDoctrineHelper(),
            $this->createMock(AnalyticsBuilder::class),
            new JobRunner(),
            $logger
        );

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, new $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testThrowIfMessageBodyInvalidJson()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The malformed json given.');

        $processor = new CalculateChannelAnalyticsProcessor(
            $this->getDoctrineHelper(),
            $this->createMock(AnalyticsBuilder::class),
            new JobRunner(),
            $this->createMock(LoggerInterface::class)
        );

        $message = new Message();
        $message->setBody('[}');

        $session = $this->createMock(SessionInterface::class);
        $processor->process($message, $session);
    }

    public function testShouldRejectMessageIfChannelNotExist()
    {
        $entityManager = $this->getEntityManager();
        $entityManager->expects($this->once())
            ->method('find')
            ->with(Channel::class, 'theChannelId')
            ->willReturn(null);

        $doctrineHelper = $this->getDoctrineHelper($entityManager);

        $analyticsBuilder = $this->createMock(AnalyticsBuilder::class);
        $analyticsBuilder->expects(self::never())
            ->method('build');

        $message = new Message();
        $message->setBody(JSON::encode(['channel_id' => 'theChannelId']));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('Channel not found: theChannelId');

        $processor = new CalculateChannelAnalyticsProcessor(
            $doctrineHelper,
            $analyticsBuilder,
            new JobRunner(),
            $logger
        );

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldRejectMessageIfChannelStatusIsNotActive()
    {
        $channel = new Channel();
        $channel->setStatus(Channel::STATUS_INACTIVE);

        $entityManager = $this->getEntityManager();
        $entityManager->expects($this->once())
            ->method('find')
            ->with(Channel::class, 'theChannelId')
            ->willReturn($channel);

        $doctrineHelper = $this->getDoctrineHelper($entityManager);

        $analyticsBuilder = $this->createMock(AnalyticsBuilder::class);
        $analyticsBuilder->expects(self::never())
            ->method('build');

        $message = new Message();
        $message->setBody(JSON::encode(['channel_id' => 'theChannelId']));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('Channel not active: theChannelId');

        $processor = new CalculateChannelAnalyticsProcessor(
            $doctrineHelper,
            $analyticsBuilder,
            new JobRunner(),
            $logger
        );

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldRejectMessageIfChannelCustomerIdentityIsNotInstanceOfAnalyticsAwareInterface()
    {
        $channel = new Channel();
        $channel->setStatus(Channel::STATUS_ACTIVE);
        $channel->setCustomerIdentity(\stdClass::class);

        $entityManager = $this->getEntityManager();
        $entityManager->expects($this->once())
            ->method('find')
            ->with(Channel::class, 'theChannelId')
            ->willReturn($channel);

        $doctrineHelper = $this->getDoctrineHelper($entityManager);

        $analyticsBuilder = $this->createMock(AnalyticsBuilder::class);
        $analyticsBuilder->expects(self::never())
            ->method('build');

        $message = new Message();
        $message->setBody(JSON::encode(['channel_id' => 'theChannelId']));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('Channel is not supposed to calculate analytics: theChannelId');

        $processor = new CalculateChannelAnalyticsProcessor(
            $doctrineHelper,
            $analyticsBuilder,
            new JobRunner(),
            $logger
        );

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldBuildAnalyticsForGivenChannelIfChannelCustomerIdentityInstanceOfAnalyticsAwareInterface()
    {
        //guard
        $this->assertClassImplements(AnalyticsAwareInterface::class, CustomerAwareStub::class);

        $channel = new Channel();
        $channel->setStatus(Channel::STATUS_ACTIVE);
        $channel->setCustomerIdentity(CustomerAwareStub::class);

        $entityManager = $this->getEntityManager();
        $entityManager->expects($this->once())
            ->method('find')
            ->with(Channel::class, 'theChannelId')
            ->willReturn($channel);

        $doctrineHelper = $this->getDoctrineHelper($entityManager);

        $analyticsBuilder = $this->createMock(AnalyticsBuilder::class);
        $analyticsBuilder->expects(self::once())
            ->method('build')
            ->with(self::identicalTo($channel), []);

        $processor = new CalculateChannelAnalyticsProcessor(
            $doctrineHelper,
            $analyticsBuilder,
            new JobRunner(),
            $this->createMock(LoggerInterface::class)
        );

        $message = new Message();
        $message->setBody(JSON::encode(['channel_id' => 'theChannelId']));

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldPassCustomerIdsToBuildMethod()
    {
        //guard
        $this->assertClassImplements(AnalyticsAwareInterface::class, CustomerAwareStub::class);

        $channel = new Channel();
        $channel->setStatus(Channel::STATUS_ACTIVE);
        $channel->setCustomerIdentity(CustomerAwareStub::class);

        $entityManager = $this->getEntityManager();
        $entityManager->expects($this->once())
            ->method('find')
            ->with(Channel::class, 'theChannelId')
            ->willReturn($channel);

        $doctrineHelper = $this->getDoctrineHelper($entityManager);

        $analyticsBuilder = $this->createMock(AnalyticsBuilder::class);
        $analyticsBuilder->expects(self::once())
            ->method('build')
            ->with(self::identicalTo($channel), ['theCustomerFooId', 'theCustomerBarId']);

        $processor = new CalculateChannelAnalyticsProcessor(
            $doctrineHelper,
            $analyticsBuilder,
            new JobRunner(),
            $this->createMock(LoggerInterface::class)
        );

        $message = new Message();
        $message->setBody(JSON::encode([
            'channel_id' => 'theChannelId',
            'customer_ids' => ['theCustomerFooId', 'theCustomerBarId'],
        ]));

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldRunCalculateAnalyticsAsUniqueJob()
    {
        //guard
        $this->assertClassImplements(AnalyticsAwareInterface::class, CustomerAwareStub::class);

        $channel = new Channel();
        $channel->setStatus(Channel::STATUS_ACTIVE);
        $channel->setCustomerIdentity(CustomerAwareStub::class);

        $entityManager = $this->getEntityManager();
        $entityManager->expects($this->once())
            ->method('find')
            ->with(Channel::class, 'theChannelId')
            ->willReturn($channel);

        $doctrineHelper = $this->getDoctrineHelper($entityManager);

        $jobRunner = new JobRunner();

        $processor = new CalculateChannelAnalyticsProcessor(
            $doctrineHelper,
            $this->createMock(AnalyticsBuilder::class),
            $jobRunner,
            $this->createMock(LoggerInterface::class)
        );

        $message = new Message();
        $message->setMessageId('theMessageId');
        $message->setBody(JSON::encode([
            'channel_id' => 'theChannelId',
            'customer_ids' => ['theCustomerFooId', 'theCustomerBarId'],
        ]));

        $session = $this->createMock(SessionInterface::class);
        $processor->process($message, $session);

        $uniqueJobs = $jobRunner->getRunUniqueJobs();
        self::assertCount(1, $uniqueJobs);
        self::assertEquals('oro_analytics:calculate_channel_analytics:theChannelId', $uniqueJobs[0]['jobName']);
        self::assertEquals('theMessageId', $uniqueJobs[0]['ownerId']);
    }

    /**
     * @return EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getEntityManager()
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->any())
            ->method('getConfiguration')
            ->willReturn(new Configuration());

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($connection);

        return $entityManager;
    }

    private function getDoctrineHelper(EntityManagerInterface $entityManager = null): DoctrineHelper
    {
        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrineHelper->expects($this->any())
            ->method('getEntityManager')
            ->willReturn($entityManager);

        return $doctrineHelper;
    }
}
