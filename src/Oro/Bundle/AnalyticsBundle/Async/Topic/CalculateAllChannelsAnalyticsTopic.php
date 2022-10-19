<?php

namespace Oro\Bundle\AnalyticsBundle\Async\Topic;

use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic to calculate all channels analytics
 */
class CalculateAllChannelsAnalyticsTopic extends AbstractTopic
{
    public static function getName(): string
    {
        return 'oro.analytics.calculate_all_channels_analytics';
    }

    public static function getDescription(): string
    {
        return 'Calculates all channels analytics.';
    }

    public function getDefaultPriority(string $queueName): string
    {
        return MessagePriority::VERY_LOW;
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
    }
}
