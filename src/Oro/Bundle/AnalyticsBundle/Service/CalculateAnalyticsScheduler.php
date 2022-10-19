<?php
namespace Oro\Bundle\AnalyticsBundle\Service;

use Oro\Bundle\AnalyticsBundle\Async\Topic\CalculateAllChannelsAnalyticsTopic;
use Oro\Bundle\AnalyticsBundle\Async\Topic\CalculateChannelAnalyticsTopic;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

/**
 * Schedules channels analytics calculation
 */
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
            CalculateChannelAnalyticsTopic::getName(),
            [
                'channel_id' => $channelId,
                'customer_ids' => $customerIds,
            ]
        );
    }

    public function scheduleForAllChannels()
    {
        $this->messageProducer->send(CalculateAllChannelsAnalyticsTopic::getName(), []);
    }
}
