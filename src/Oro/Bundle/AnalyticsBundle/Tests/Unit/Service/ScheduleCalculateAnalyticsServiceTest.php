<?php
namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Service;

use Oro\Bundle\AnalyticsBundle\Async\Topics;
use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\MessageQueueBundle\Test\Unit\MessageQueueExtension;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

class ScheduleCalculateAnalyticsServiceTest extends \PHPUnit\Framework\TestCase
{
    use MessageQueueExtension;

    public function testCouldBeConstructedWithMessageProducerAsFirstArgument()
    {
        new CalculateAnalyticsScheduler($this->createMock(MessageProducerInterface::class));
    }

    public function testShouldSendCalculateAnalyticsForSingleChannel()
    {
        $scheduler = new CalculateAnalyticsScheduler(self::getMessageProducer());

        $scheduler->scheduleForChannel('theChannelId');

        self::assertMessageSent(
            Topics::CALCULATE_CHANNEL_ANALYTICS,
            new Message(
                [
                    'channel_id' => 'theChannelId',
                    'customer_ids' => [],
                ],
                MessagePriority::VERY_LOW
            )
        );
    }

    public function testShouldSendCalculateAnalyticsForSingleChannelAndCustomCustomers()
    {
        $scheduler = new CalculateAnalyticsScheduler(self::getMessageProducer());

        $scheduler->scheduleForChannel('theChannelId', ['theCustomerFooId', 'theCustomerBarId']);

        self::assertMessageSent(
            Topics::CALCULATE_CHANNEL_ANALYTICS,
            new Message(
                [
                    'channel_id' => 'theChannelId',
                    'customer_ids' => ['theCustomerFooId', 'theCustomerBarId'],
                ],
                MessagePriority::VERY_LOW
            )
        );
    }

    public function testShouldSendCalculateAllChannelsAnalytics()
    {
        $scheduler = new CalculateAnalyticsScheduler(self::getMessageProducer());

        $scheduler->scheduleForAllChannels();

        self::assertMessageSent(
            Topics::CALCULATE_ALL_CHANNELS_ANALYTICS,
            new Message([], MessagePriority::VERY_LOW)
        );
    }
}
