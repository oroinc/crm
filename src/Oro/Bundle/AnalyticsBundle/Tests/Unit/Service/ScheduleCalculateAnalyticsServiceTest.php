<?php
namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Service;

use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageCollector;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Bundle\AnalyticsBundle\Async\Topics;
use Oro\Bundle\AnalyticsBundle\Service\ScheduleCalculateAnalyticsService;

class ScheduleCalculateAnalyticsServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testCouldBeConstructedWithMessageProducerAsFirstArgument()
    {
        new ScheduleCalculateAnalyticsService($this->getMock(MessageProducerInterface::class));
    }

    public function testShouldSendCalculateAnalyticsForSingleChannel()
    {
        $producer = $this->createMessageProducer();

        $service = new ScheduleCalculateAnalyticsService($producer);

        $service->scheduleForChannel('theChannelId');

        $traces = $producer->getTopicSentMessages(Topics::CALCULATE_CHANNEL_ANALYTICS);

        self::assertCount(1, $traces);
        self::assertEquals([
            'channel_id' => 'theChannelId',
            'customer_ids' => [],
        ], $traces[0]['message']->getBody());
        self::assertEquals(MessagePriority::VERY_LOW, $traces[0]['message']->getPriority());
    }

    public function testShouldSendCalculateAnalyticsForSingleChannelAndCustomCustomers()
    {
        $producer = $this->createMessageProducer();

        $service = new ScheduleCalculateAnalyticsService($producer);

        $service->scheduleForChannel('theChannelId', ['theCustomerFooId', 'theCustomerBarId']);

        $traces = $producer->getTopicSentMessages(Topics::CALCULATE_CHANNEL_ANALYTICS);

        self::assertCount(1, $traces);
        self::assertEquals([
            'channel_id' => 'theChannelId',
            'customer_ids' => ['theCustomerFooId', 'theCustomerBarId'],
        ], $traces[0]['message']->getBody());
        self::assertEquals(MessagePriority::VERY_LOW, $traces[0]['message']->getPriority());
    }

    public function testShouldSendCalculateAllChannelsAnalytics()
    {
        $producer = $this->createMessageProducer();

        $service = new ScheduleCalculateAnalyticsService($producer);

        $service->scheduleForAllChannels();

        $traces = $producer->getTopicSentMessages(Topics::CALCULATE_ALL_CHANNELS_ANALYTICS);

        self::assertCount(1, $traces);
        self::assertEquals([], $traces[0]['message']->getBody());
        self::assertEquals(MessagePriority::VERY_LOW, $traces[0]['message']->getPriority());
    }

    /**
     * @return MessageCollector
     */
    private function createMessageProducer()
    {
        $collector = new MessageCollector($this->getMock(MessageProducerInterface::class));

        return $collector;
    }
}
