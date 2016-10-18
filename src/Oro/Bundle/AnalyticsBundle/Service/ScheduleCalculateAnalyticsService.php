<?php
namespace Oro\Bundle\AnalyticsBundle\Service;

use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Bundle\AnalyticsBundle\Async\Topics;

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
        $message = new Message();
        $message->setPriority(MessagePriority::VERY_LOW);
        $message->setBody([
            'channel_id' => $channelId,
            'customer_ids' => $customerIds,
        ]);

        $this->messageProducer->send(Topics::CALCULATE_CHANNEL_ANALYTICS, $message);
    }

    public function scheduleForAllChannels()
    {
        $message = new Message();
        $message->setPriority(MessagePriority::VERY_LOW);
        $message->setBody([]);

        $this->messageProducer->send(Topics::CALCULATE_ALL_CHANNELS_ANALYTICS, $message);
    }
}
