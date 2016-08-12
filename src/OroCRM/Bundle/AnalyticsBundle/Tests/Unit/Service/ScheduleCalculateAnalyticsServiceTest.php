<?php
namespace OroCRM\Bundle\AnalyticsBundle\Tests\Unit\Service;

use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Client\TraceableMessageProducer;
use OroCRM\Bundle\AnalyticsBundle\Async\Topics;
use OroCRM\Bundle\AnalyticsBundle\Service\ScheduleCalculateAnalyticsService;

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

        $traces = $producer->getTopicTraces(Topics::CALCULATE_CHANNEL_ANALYTICS);

        self::assertCount(1, $traces);
        self::assertEquals([
            'channel_id' => 'theChannelId',
            'customer_ids' => [],
        ], $traces[0]['message']);
        self::assertEquals(MessagePriority::VERY_LOW, $traces[0]['priority']);
    }

    public function testShouldSendCalculateAnalyticsForSingleChannelAndCustomCustomers()
    {
        $producer = $this->createMessageProducer();

        $service = new ScheduleCalculateAnalyticsService($producer);

        $service->scheduleForChannel('theChannelId', ['theCustomerFooId', 'theCustomerBarId']);

        $traces = $producer->getTopicTraces(Topics::CALCULATE_CHANNEL_ANALYTICS);

        self::assertCount(1, $traces);
        self::assertEquals([
            'channel_id' => 'theChannelId',
            'customer_ids' => ['theCustomerFooId', 'theCustomerBarId'],
        ], $traces[0]['message']);
        self::assertEquals(MessagePriority::VERY_LOW, $traces[0]['priority']);
    }

    public function testShouldSendCalculateAllChannelsAnalytics()
    {
        $producer = $this->createMessageProducer();

        $service = new ScheduleCalculateAnalyticsService($producer);

        $service->scheduleForAllChannels();

        $traces = $producer->getTopicTraces(Topics::CALCULATE_ALL_CHANNELS_ANALYTICS);

        self::assertCount(1, $traces);
        self::assertEquals([], $traces[0]['message']);
        self::assertEquals(MessagePriority::VERY_LOW, $traces[0]['priority']);
    }

    /**
     * @return TraceableMessageProducer
     */
    private function createMessageProducer()
    {
        return new TraceableMessageProducer($this->getMock(MessageProducerInterface::class));
    }
}
