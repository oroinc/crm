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
            $this->createDoctrineHelperStub(),
            $this->createAnalyticsBuilder(),
            new JobRunner(),
            $this->createLoggerMock()
        );
    }

    public function testShouldLogAndRejectIfMessageBodyMissChannelId()
    {
        $message = new Message();
        $message->setBody('[]');

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('critical')
            ->with('The message invalid. It must have channel_id set')
        ;

        $processor = new CalculateChannelAnalyticsProcessor(
            $this->createDoctrineHelperStub(),
            $this->createAnalyticsBuilder(),
            new JobRunner(),
            $logger
        );

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, new $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testThrowIfMessageBodyInvalidJson()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The malformed json given.');

        $processor = new CalculateChannelAnalyticsProcessor(
            $this->createDoctrineHelperStub(),
            $this->createAnalyticsBuilder(),
            new JobRunner(),
            $this->createLoggerMock()
        );

        $message = new Message();
        $message->setBody('[}');

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $processor->process($message, $session);
    }

    public function testShouldRejectMessageIfChannelNotExist()
    {
        $entityManagerMock = $this->createEntityManagerStub();
        $entityManagerMock
            ->expects($this->once())
            ->method('find')
            ->with(Channel::class, 'theChannelId')
            ->willReturn(null);
        ;

        $doctrineHelperStub = $this->createDoctrineHelperStub($entityManagerMock);

        $analyticsBuilderMock = $this->createAnalyticsBuilder();
        $analyticsBuilderMock
            ->expects(self::never())
            ->method('build')
        ;

        $message = new Message();
        $message->setBody(JSON::encode(['channel_id' => 'theChannelId']));

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('Channel not found: theChannelId')
        ;

        $processor = new CalculateChannelAnalyticsProcessor(
            $doctrineHelperStub,
            $analyticsBuilderMock,
            new JobRunner(),
            $logger
        );

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldRejectMessageIfChannelStatusIsNotActive()
    {
        $channel = new Channel();
        $channel->setStatus(Channel::STATUS_INACTIVE);

        $entityManagerMock = $this->createEntityManagerStub();
        $entityManagerMock
            ->expects($this->once())
            ->method('find')
            ->with(Channel::class, 'theChannelId')
            ->willReturn($channel);
        ;

        $doctrineHelperStub = $this->createDoctrineHelperStub($entityManagerMock);

        $analyticsBuilderMock = $this->createAnalyticsBuilder();
        $analyticsBuilderMock
            ->expects(self::never())
            ->method('build')
        ;

        $message = new Message();
        $message->setBody(JSON::encode(['channel_id' => 'theChannelId']));

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('Channel not active: theChannelId')
        ;

        $processor = new CalculateChannelAnalyticsProcessor(
            $doctrineHelperStub,
            $analyticsBuilderMock,
            new JobRunner(),
            $logger
        );

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldRejectMessageIfChannelCustomerIdentityIsNotInstanceOfAnalyticsAwareInterface()
    {
        $channel = new Channel();
        $channel->setStatus(Channel::STATUS_ACTIVE);
        $channel->setCustomerIdentity(\stdClass::class);

        $entityManagerMock = $this->createEntityManagerStub();
        $entityManagerMock
            ->expects($this->once())
            ->method('find')
            ->with(Channel::class, 'theChannelId')
            ->willReturn($channel);
        ;

        $doctrineHelperStub = $this->createDoctrineHelperStub($entityManagerMock);

        $analyticsBuilderMock = $this->createAnalyticsBuilder();
        $analyticsBuilderMock
            ->expects(self::never())
            ->method('build')
        ;

        $message = new Message();
        $message->setBody(JSON::encode(['channel_id' => 'theChannelId']));

        $logger = $this->createLoggerMock();
        $logger
            ->expects($this->once())
            ->method('error')
            ->with('Channel is not supposed to calculate analytics: theChannelId')
        ;

        $processor = new CalculateChannelAnalyticsProcessor(
            $doctrineHelperStub,
            $analyticsBuilderMock,
            new JobRunner(),
            $logger
        );

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::REJECT, $status);
    }

    public function testShouldBuildAnalyticsForGivenChannelIfChannelCustomerIdentityInstanceOfAnalyticsAwareInterface()
    {
        //guard
        self::assertClassImplements(AnalyticsAwareInterface::class, CustomerAwareStub::class);

        $channel = new Channel();
        $channel->setStatus(Channel::STATUS_ACTIVE);
        $channel->setCustomerIdentity(CustomerAwareStub::class);

        $entityManagerMock = $this->createEntityManagerStub();
        $entityManagerMock
            ->expects($this->once())
            ->method('find')
            ->with(Channel::class, 'theChannelId')
            ->willReturn($channel);
        ;

        $doctrineHelperStub = $this->createDoctrineHelperStub($entityManagerMock);

        $analyticsBuilderMock = $this->createAnalyticsBuilder();
        $analyticsBuilderMock
            ->expects(self::once())
            ->method('build')
            ->with(self::identicalTo($channel), [])
        ;

        $processor = new CalculateChannelAnalyticsProcessor(
            $doctrineHelperStub,
            $analyticsBuilderMock,
            new JobRunner(),
            $this->createLoggerMock()
        );

        $message = new Message();
        $message->setBody(JSON::encode(['channel_id' => 'theChannelId']));

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldPassCustomerIdsToBuildMethod()
    {
        //guard
        self::assertClassImplements(AnalyticsAwareInterface::class, CustomerAwareStub::class);

        $channel = new Channel();
        $channel->setStatus(Channel::STATUS_ACTIVE);
        $channel->setCustomerIdentity(CustomerAwareStub::class);

        $entityManagerMock = $this->createEntityManagerStub();
        $entityManagerMock
            ->expects($this->once())
            ->method('find')
            ->with(Channel::class, 'theChannelId')
            ->willReturn($channel);
        ;

        $doctrineHelperStub = $this->createDoctrineHelperStub($entityManagerMock);

        $analyticsBuilderMock = $this->createAnalyticsBuilder();
        $analyticsBuilderMock
            ->expects(self::once())
            ->method('build')
            ->with(self::identicalTo($channel), ['theCustomerFooId', 'theCustomerBarId'])
        ;

        $processor = new CalculateChannelAnalyticsProcessor(
            $doctrineHelperStub,
            $analyticsBuilderMock,
            new JobRunner(),
            $this->createLoggerMock()
        );

        $message = new Message();
        $message->setBody(JSON::encode([
            'channel_id' => 'theChannelId',
            'customer_ids' => ['theCustomerFooId', 'theCustomerBarId'],
        ]));

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldRunCalculateAnalyticsAsUniqueJob()
    {
        //guard
        self::assertClassImplements(AnalyticsAwareInterface::class, CustomerAwareStub::class);

        $channel = new Channel();
        $channel->setStatus(Channel::STATUS_ACTIVE);
        $channel->setCustomerIdentity(CustomerAwareStub::class);

        $entityManagerMock = $this->createEntityManagerStub();
        $entityManagerMock
            ->expects($this->once())
            ->method('find')
            ->with(Channel::class, 'theChannelId')
            ->willReturn($channel);
        ;

        $doctrineHelperStub = $this->createDoctrineHelperStub($entityManagerMock);

        $jobRunner = new JobRunner();

        $processor = new CalculateChannelAnalyticsProcessor(
            $doctrineHelperStub,
            $this->createAnalyticsBuilder(),
            $jobRunner,
            $this->createLoggerMock()
        );

        $message = new Message();
        $message->setMessageId('theMessageId');
        $message->setBody(JSON::encode([
            'channel_id' => 'theChannelId',
            'customer_ids' => ['theCustomerFooId', 'theCustomerBarId'],
        ]));

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $processor->process($message, $session);

        $uniqueJobs = $jobRunner->getRunUniqueJobs();
        self::assertCount(1, $uniqueJobs);
        self::assertEquals('oro_analytics:calculate_channel_analytics:theChannelId', $uniqueJobs[0]['jobName']);
        self::assertEquals('theMessageId', $uniqueJobs[0]['ownerId']);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|EntityManagerInterface
     */
    private function createEntityManagerStub()
    {
        $configuration = new Configuration();

        $connectionMock = $this->createMock(Connection::class);
        $connectionMock
            ->expects($this->any())
            ->method('getConfiguration')
            ->willReturn($configuration)
        ;

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionMock)
        ;

        return $entityManagerMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|AnalyticsBuilder
     */
    private function createAnalyticsBuilder()
    {
        return $this->createMock(AnalyticsBuilder::class);
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject|EntityManagerInterface $entityManager
     * @return \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper
     */
    private function createDoctrineHelperStub($entityManager = null)
    {
        $helperMock = $this->createMock(DoctrineHelper::class);
        $helperMock
            ->expects($this->any())
            ->method('getEntityManager')
            ->willReturn($entityManager)
        ;

        return $helperMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject | LoggerInterface
     */
    private function createLoggerMock()
    {
        return $this->createMock(LoggerInterface::class);
    }
}
