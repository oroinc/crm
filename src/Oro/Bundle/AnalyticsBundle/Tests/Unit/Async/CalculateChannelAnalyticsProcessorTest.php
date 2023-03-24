<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Async;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\AnalyticsBundle\Async\CalculateChannelAnalyticsProcessor;
use Oro\Bundle\AnalyticsBundle\Async\Topic\CalculateChannelAnalyticsTopic;
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
use Oro\Component\Testing\ClassExtensionTrait;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CalculateChannelAnalyticsProcessorTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProcessorInterface(): void
    {
        $this->assertClassImplements(MessageProcessorInterface::class, CalculateChannelAnalyticsProcessor::class);
    }

    public function testShouldImplementTopicSubscriberInterface(): void
    {
        $this->assertClassImplements(TopicSubscriberInterface::class, CalculateChannelAnalyticsProcessor::class);
    }

    public function testShouldSubscribeOnCalculateChannelAnalyticsTopic(): void
    {
        self::assertEquals(
            [CalculateChannelAnalyticsTopic::getName()],
            CalculateChannelAnalyticsProcessor::getSubscribedTopics()
        );
    }

    public function testCouldBeConstructedWithExpectedArguments(): void
    {
        new CalculateChannelAnalyticsProcessor(
            $this->getDoctrineHelper(),
            $this->createMock(AnalyticsBuilder::class),
            new JobRunner(),
            $this->createMock(LoggerInterface::class)
        );
    }

    public function testShouldRejectMessageIfChannelNotExist(): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->expects(self::once())
            ->method('find')
            ->with(Channel::class, 1)
            ->willReturn(null);

        $doctrineHelper = $this->getDoctrineHelper($entityManager);

        $analyticsBuilder = $this->createMock(AnalyticsBuilder::class);
        $analyticsBuilder->expects(self::never())
            ->method('build');

        $message = new Message();
        $message->setBody(['channel_id' => 1]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with('Channel not found: 1');

        $processor = new CalculateChannelAnalyticsProcessor(
            $doctrineHelper,
            $analyticsBuilder,
            new JobRunner(),
            $logger
        );

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        self::assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldRejectMessageIfChannelStatusIsNotActive(): void
    {
        $channel = new Channel();
        $channel->setStatus(Channel::STATUS_INACTIVE);

        $entityManager = $this->getEntityManager();
        $entityManager->expects(self::once())
            ->method('find')
            ->with(Channel::class, 1)
            ->willReturn($channel);

        $doctrineHelper = $this->getDoctrineHelper($entityManager);

        $analyticsBuilder = $this->createMock(AnalyticsBuilder::class);
        $analyticsBuilder->expects(self::never())
            ->method('build');

        $message = new Message();
        $message->setBody(['channel_id' => 1]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with('Channel not active: 1');

        $processor = new CalculateChannelAnalyticsProcessor(
            $doctrineHelper,
            $analyticsBuilder,
            new JobRunner(),
            $logger
        );

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        self::assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldRejectMessageIfChannelCustomerIdentityIsNotInstanceOfAnalyticsAwareInterface(): void
    {
        $channel = new Channel();
        $channel->setStatus(Channel::STATUS_ACTIVE);
        $channel->setCustomerIdentity(\stdClass::class);

        $entityManager = $this->getEntityManager();
        $entityManager->expects(self::once())
            ->method('find')
            ->with(Channel::class, 1)
            ->willReturn($channel);

        $doctrineHelper = $this->getDoctrineHelper($entityManager);

        $analyticsBuilder = $this->createMock(AnalyticsBuilder::class);
        $analyticsBuilder->expects(self::never())
            ->method('build');

        $message = new Message();
        $message->setBody(['channel_id' => 1]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with('Channel is not supposed to calculate analytics: 1');

        $processor = new CalculateChannelAnalyticsProcessor(
            $doctrineHelper,
            $analyticsBuilder,
            new JobRunner(),
            $logger
        );

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        self::assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldBuildAnalyticsForGivenChannelIfChannelCustomerIdentityInstanceOfAnalyticsAwareInterface()
    {
        //guard
        $this->assertClassImplements(AnalyticsAwareInterface::class, CustomerAwareStub::class);

        $channel = new Channel();
        $channel->setStatus(Channel::STATUS_ACTIVE);
        $channel->setCustomerIdentity(CustomerAwareStub::class);

        $entityManager = $this->getEntityManager();
        $entityManager->expects(self::once())
            ->method('find')
            ->with(Channel::class, 1)
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
        $message->setBody([
            'channel_id' => 1,
            'customer_ids' => [],
        ]);

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        self::assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldPassCustomerIdsToBuildMethod(): void
    {
        //guard
        $this->assertClassImplements(AnalyticsAwareInterface::class, CustomerAwareStub::class);

        $channel = new Channel();
        $channel->setStatus(Channel::STATUS_ACTIVE);
        $channel->setCustomerIdentity(CustomerAwareStub::class);

        $entityManager = $this->getEntityManager();
        $entityManager->expects(self::once())
            ->method('find')
            ->with(Channel::class, 1)
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
        $message->setBody([
            'channel_id' => 1,
            'customer_ids' => ['theCustomerFooId', 'theCustomerBarId'],
        ]);

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        self::assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldRunCalculateAnalyticsAsUniqueJob(): void
    {
        //guard
        $this->assertClassImplements(AnalyticsAwareInterface::class, CustomerAwareStub::class);

        $channel = new Channel();
        $channel->setStatus(Channel::STATUS_ACTIVE);
        $channel->setCustomerIdentity(CustomerAwareStub::class);

        $entityManager = $this->getEntityManager();
        $entityManager->expects(self::once())
            ->method('find')
            ->with(Channel::class, 1)
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
        $message->setBody([
            'channel_id' => 1,
            'customer_ids' => ['theCustomerFooId', 'theCustomerBarId'],
        ]);

        $session = $this->createMock(SessionInterface::class);
        $processor->process($message, $session);

        $uniqueJobs = $jobRunner->getRunUniqueJobs();
        self::assertCount(1, $uniqueJobs);
        self::assertEquals('theMessageId', $uniqueJobs[0]['ownerId']);
    }

    /**
     * @return EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getEntityManager()
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::any())
            ->method('getConfiguration')
            ->willReturn(new Configuration());

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::any())
            ->method('getConnection')
            ->willReturn($connection);

        return $entityManager;
    }

    private function getDoctrineHelper(EntityManagerInterface $entityManager = null): DoctrineHelper
    {
        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrineHelper->expects(self::any())
            ->method('getEntityManager')
            ->willReturn($entityManager);

        return $doctrineHelper;
    }
}
