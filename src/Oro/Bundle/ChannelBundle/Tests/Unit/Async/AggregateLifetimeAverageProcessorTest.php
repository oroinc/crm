<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Async;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\ChannelBundle\Async\AggregateLifetimeAverageProcessor;
use Oro\Bundle\ChannelBundle\Async\Topic\AggregateLifetimeAverageTopic;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeValueAverageAggregationRepository;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Test\JobRunner;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\Testing\ClassExtensionTrait;

class AggregateLifetimeAverageProcessorTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProcessorInterface(): void
    {
        $this->assertClassImplements(MessageProcessorInterface::class, AggregateLifetimeAverageProcessor::class);
    }

    public function testShouldImplementTopicSubscriberInterface(): void
    {
        $this->assertClassImplements(TopicSubscriberInterface::class, AggregateLifetimeAverageProcessor::class);
    }

    public function testShouldSubscribeOnChannelStatusChangedTopic(): void
    {
        self::assertEquals(
            [AggregateLifetimeAverageTopic::getName()],
            AggregateLifetimeAverageProcessor::getSubscribedTopics()
        );
    }

    public function testCouldBeConstructedWithDoctrineAndLocaleSettingsAsArguments(): void
    {
        new AggregateLifetimeAverageProcessor(
            $this->getDoctrine(),
            $this->createMock(LocaleSettings::class),
            new JobRunner()
        );
    }

    public function testShouldDoAggregateAndWithoutForceWithDefaultValues(): void
    {
        $localeSettings = $this->createMock(LocaleSettings::class);
        $localeSettings->expects(self::once())
            ->method('getTimeZone')
            ->willReturn('theTimeZone');

        $repository = $this->createMock(LifetimeValueAverageAggregationRepository::class);
        $repository->expects(self::never())
            ->method('clearTableData');
        $repository->expects(self::once())
            ->method('aggregate')
            ->with('theTimeZone', false);

        $processor = new AggregateLifetimeAverageProcessor(
            $this->getDoctrine($repository),
            $localeSettings,
            new JobRunner()
        );

        $message = new Message();
        $message->setBody([
            'force' => false,
            'use_truncate' => true,
        ]);

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        self::assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldClearTableBeforeAggregateIfForceTrue(): void
    {
        $localeSettings = $this->createMock(LocaleSettings::class);
        $localeSettings->expects(self::once())
            ->method('getTimeZone')
            ->willReturn('theTimeZone');

        $repository = $this->createMock(LifetimeValueAverageAggregationRepository::class);
        $repository->expects(self::once())
            ->method('clearTableData')
            ->with(false);
        $repository->expects(self::once())
            ->method('aggregate')
            ->with('theTimeZone', true);

        $processor = new AggregateLifetimeAverageProcessor(
            $this->getDoctrine($repository),
            $localeSettings,
            new JobRunner()
        );

        $message = new Message();
        $message->setBody([
            'force' => true,
            'use_truncate' => true,
        ]);

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        self::assertEquals(MessageProcessorInterface::ACK, $status);
    }

    public function testShouldTruncateTableBeforeAggregateIfForceTrue(): void
    {
        $localeSettings = $this->createMock(LocaleSettings::class);
        $localeSettings->expects(self::once())
            ->method('getTimeZone')
            ->willReturn('theTimeZone');

        $repository = $this->createMock(LifetimeValueAverageAggregationRepository::class);
        $repository->expects(self::once())
            ->method('clearTableData')
            ->with(true);
        $repository->expects(self::once())
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
        $message->setBody([
            'force' => true,
            'use_truncate' => false,
        ]);

        $session = $this->createMock(SessionInterface::class);
        $status = $processor->process($message, $session);

        self::assertEquals(MessageProcessorInterface::ACK, $status);

        $uniqueJobs = $jobRunner->getRunUniqueJobs();
        self::assertCount(1, $uniqueJobs);
        self::assertEquals('theMessageId', $uniqueJobs[0]['ownerId']);
    }

    private function getDoctrine(ObjectRepository $repository = null): ManagerRegistry
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(self::any())
            ->method('getRepository')
            ->willReturn($repository);

        return $registry;
    }
}
