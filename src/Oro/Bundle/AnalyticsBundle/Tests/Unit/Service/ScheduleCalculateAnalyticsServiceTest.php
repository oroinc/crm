<?php
namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Service;

use Oro\Bundle\AnalyticsBundle\Async\Topic\CalculateAllChannelsAnalyticsTopic;
use Oro\Bundle\AnalyticsBundle\Async\Topic\CalculateChannelAnalyticsTopic;
use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\MessageQueueBundle\Test\Unit\MessageQueueExtension;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

class ScheduleCalculateAnalyticsServiceTest extends \PHPUnit\Framework\TestCase
{
    use MessageQueueExtension;

    public function testCouldBeConstructedWithMessageProducerAsFirstArgument(): void
    {
        new CalculateAnalyticsScheduler($this->createMock(MessageProducerInterface::class));
    }

    public function testShouldSendCalculateAnalyticsForSingleChannel(): void
    {
        $scheduler = new CalculateAnalyticsScheduler(self::getMessageProducer());

        $scheduler->scheduleForChannel('theChannelId');

        self::assertMessageSent(
            CalculateChannelAnalyticsTopic::getName(),
            [
                'channel_id' => 'theChannelId',
                'customer_ids' => [],
            ]
        );
    }

    public function testShouldSendCalculateAnalyticsForSingleChannelAndCustomCustomers(): void
    {
        $scheduler = new CalculateAnalyticsScheduler(self::getMessageProducer());

        $scheduler->scheduleForChannel('theChannelId', ['theCustomerFooId', 'theCustomerBarId']);

        self::assertMessageSent(
            CalculateChannelAnalyticsTopic::getName(),
            [
                'channel_id' => 'theChannelId',
                'customer_ids' => ['theCustomerFooId', 'theCustomerBarId'],
            ]
        );
    }

    public function testShouldSendCalculateAllChannelsAnalytics(): void
    {
        $scheduler = new CalculateAnalyticsScheduler(self::getMessageProducer());

        $scheduler->scheduleForAllChannels();

        self::assertMessageSent(CalculateAllChannelsAnalyticsTopic::getName(), []);
    }
}
