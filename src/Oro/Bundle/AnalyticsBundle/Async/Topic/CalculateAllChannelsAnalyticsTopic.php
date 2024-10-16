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
    #[\Override]
    public static function getName(): string
    {
        return 'oro.analytics.calculate_all_channels_analytics';
    }

    #[\Override]
    public static function getDescription(): string
    {
        return 'Calculates all channels analytics.';
    }

    #[\Override]
    public function getDefaultPriority(string $queueName): string
    {
        return MessagePriority::VERY_LOW;
    }

    #[\Override]
    public function configureMessageBody(OptionsResolver $resolver): void
    {
    }
}
