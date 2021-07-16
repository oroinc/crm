<?php
namespace Oro\Bundle\AnalyticsBundle\Service;

use Oro\Bundle\AnalyticsBundle\Async\Topics;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

class CalculateAnalyticsScheduler
{
    /**
     * @var MessageProducerInterface
     */
    private $messageProducer;

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
        $this->messageProducer->send(
            Topics::CALCULATE_CHANNEL_ANALYTICS,
            new Message(
                [
                    'channel_id' => $channelId,
                    'customer_ids' => $customerIds,
                ],
                MessagePriority::VERY_LOW
            )
        );
    }

    public function scheduleForAllChannels()
    {
        $this->messageProducer->send(
            Topics::CALCULATE_ALL_CHANNELS_ANALYTICS,
            new Message([], MessagePriority::VERY_LOW)
        );
    }
}
