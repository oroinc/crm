<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Async;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\ChannelBundle\Async\AggregateLifetimeAverageProcessor;
use Oro\Bundle\ChannelBundle\Async\Topics;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeValueAverageAggregationRepository;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Test\JobRunner;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Component\Testing\ClassExtensionTrait;

class AggregateLifetimeAverageProcessorTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProcessorInterface()
    {
        $this->assertClassImplements(MessageProcessorInterface::class, AggregateLifetimeAverageProcessor::class);
    }

    public function testShouldImplementTopicSubscriberInterface()
    {
        $this->assertClassImplements(TopicSubscriberInterface::class, AggregateLifetimeAverageProcessor::class);
    }

    public function testShouldSubscribeOnChannelStatusChangedTopic()
    {
        $this->assertEquals(
            [Topics::AGGREGATE_LIFETIME_AVERAGE],
            AggregateLifetimeAverageProcessor::getSubscribedTopics()
        );
    }

    public function testCouldBeConstructedWithDoctrineAndLocaleSettingsAsArguments()
    {
        new AggregateLifetimeAverageProcessor(
            $this->getDoctrine(),
            $this->createMock(LocaleSettings::class),
            new JobRunner()
        );
    }

    public function testThrowIfMessageBodyInvalidJson()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The malformed json given.');

        $processor = new AggregateLifetimeAverageProcessor(
            $this->getDoctrine(),
            $this->createMock(LocaleSettings::class),
            new JobRunner()
        );

        $message = new Message();
        $message->setBody('[}');

        $session = $this->createMock(SessionInterface::class);
        $processor->process($message, $session);
    }

    public function testShouldDoAggregateAndWithoutForceByDefault()
    {
        $localeSettings = $this->createMock(LocaleSettings::class);
        $localeSettings->expects($this->once())
            ->method('getTimeZone')
            ->willReturn('theTimeZone');

        $repository = $this->createMock(LifetimeValueAverageAggregationRepository::class);
        $repository->expects($this->never())
            ->method('clearTableData');
        $repository->expects($this->once())
            ->method('aggregate')
            ->with('theTimeZone', false);

        $processor = new AggregateLifetimeAverageProcessor(
            $this->getDoctrine($repository),
            $localeSettings,
            new JobRunner()
        );

        $message = new Message();
        $message->setBody(JSON::encode([]));

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldClearTableBeforeAggregateIfForceTrue()
    {
        $localeSettings = $this->createMock(LocaleSettings::class);
        $localeSettings->expects($this->once())
            ->method('getTimeZone')
            ->willReturn('theTimeZone');

        $repository = $this->createMock(LifetimeValueAverageAggregationRepository::class);
        $repository->expects($this->once())
            ->method('clearTableData')
            ->with(false);
        $repository->expects($this->once())
            ->method('aggregate')
            ->with('theTimeZone', true);

        $processor = new AggregateLifetimeAverageProcessor(
            $this->getDoctrine($repository),
            $localeSettings,
            new JobRunner()
        );

        $message = new Message();
        $message->setBody(JSON::encode([
            'force' => true
        ]));

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldTruncateTableBeforeAggregateIfForceTrue()
    {
        $localeSettings = $this->createMock(LocaleSettings::class);
        $localeSettings->expects($this->once())
            ->method('getTimeZone')
            ->willReturn('theTimeZone');

        $repository = $this->createMock(LifetimeValueAverageAggregationRepository::class);
        $repository->expects($this->once())
            ->method('clearTableData')
            ->with(true);
        $repository->expects($this->once())
            ->method('aggregate')
            ->with('theTimeZone', true);

        $processor = new AggregateLifetimeAverageProcessor(
            $this->getDoctrine($repository),
            $localeSettings,
            new JobRunner()
        );

        $message = new Message();
        $message->setBody(JSON::encode([
            'force' => true,
            'use_truncate' => false,
        ]));

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        $this->assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldRunAggregateLifetimeAverageAsUniqueJob()
    {
        $localeSettings = $this->createMock(LocaleSettings::class);
        $localeSettings->expects($this->once())
            ->method('getTimeZone')
            ->willReturn('theTimeZone');

        $repository = $this->createMock(LifetimeValueAverageAggregationRepository::class);
        $repository->expects($this->once())
            ->method('clearTableData')
            ->with(true);
        $repository->expects($this->once())
            ->method('aggregate')
            ->with('theTimeZone', true);

        $jobRunner = new JobRunner();

        $processor = new AggregateLifetimeAverageProcessor(
            $this->getDoctrine($repository),
            $localeSettings,
            $jobRunner
        );

        $message = new Message();
        $message->setMessageId('theMessageId');
        $message->setBody(JSON::encode([
            'force' => true,
            'use_truncate' => false,
        ]));

        $session = $this->createMock(SessionInterface::class);
        $processor->process($message, $session);

        $uniqueJobs = $jobRunner->getRunUniqueJobs();
        self::assertCount(1, $uniqueJobs);
        self::assertEquals('oro_channel:aggregate_lifetime_average', $uniqueJobs[0]['jobName']);
        self::assertEquals('theMessageId', $uniqueJobs[0]['ownerId']);
    }

    private function getDoctrine(ObjectRepository $repository = null): ManagerRegistry
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->any())
            ->method('getRepository')
            ->willReturn($repository);

        return $registry;
    }
}
