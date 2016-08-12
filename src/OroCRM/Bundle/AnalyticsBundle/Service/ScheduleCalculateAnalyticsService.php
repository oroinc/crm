<?php
namespace OroCRM\Bundle\AnalyticsBundle\Service;

use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use OroCRM\Bundle\AnalyticsBundle\Async\Topics;

class ScheduleCalculateAnalyticsService
{
    /**
     * @var MessageProducerInterface
     */
    private $messageProducer;

    /**
     * @param MessageProducerInterface $messageProducer
     */
    public function __construct(MessageProducerInterface $messageProducer)
    {
        $this->messageProducer = $messageProducer;
    }

    /**
     * @param int $channelId
     * @param int[] $customerIds
     */
    public function scheduleForChannel($channelId, array $customerIds = [])
    {
        $this->messageProducer->send(Topics::CALCULATE_CHANNEL_ANALYTICS, [
            'channel_id' => $channelId,
            'customer_ids' => $customerIds,
        ], MessagePriority::VERY_LOW);
    }

    public function scheduleForAllChannels()
    {
        $this->messageProducer->send(Topics::CALCULATE_ALL_CHANNELS_ANALYTICS, [], MessagePriority::VERY_LOW);
    }
}
