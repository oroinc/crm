<?php
namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Async;

use Oro\Bundle\AnalyticsBundle\Async\CalculateAllChannelsAnalyticsProcessor;
use Oro\Bundle\AnalyticsBundle\Async\Topic\CalculateAllChannelsAnalyticsTopic;
use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\Testing\ClassExtensionTrait;

class CalculateAllChannelsAnalyticsProcessorTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProcessorInterface(): void
    {
        $this->assertClassImplements(MessageProcessorInterface::class, CalculateAllChannelsAnalyticsProcessor::class);
    }

    public function testShouldImplementTopicSubscriberInterface(): void
    {
        $this->assertClassImplements(TopicSubscriberInterface::class, CalculateAllChannelsAnalyticsProcessor::class);
    }

    public function testShouldSubscribeOnCalculateAllChannelsAnalyticsTopic(): void
    {
        $this->assertEquals(
            [CalculateAllChannelsAnalyticsTopic::getName()],
            CalculateAllChannelsAnalyticsProcessor::getSubscribedTopics()
        );
    }

    public function testCouldBeConstructedWithExpectedArguments(): void
    {
        new CalculateAllChannelsAnalyticsProcessor(
            $this->createMock(DoctrineHelper::class),
            $this->createMock(CalculateAnalyticsScheduler::class)
        );
    }
}
