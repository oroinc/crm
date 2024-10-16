<?php

namespace Oro\Bundle\ChannelBundle\Async\Topic;

use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Oro\Component\MessageQueue\Topic\JobAwareTopicInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic to aggregate an average lifetime value
 */
class AggregateLifetimeAverageTopic extends AbstractTopic implements JobAwareTopicInterface
{
    #[\Override]
    public static function getName(): string
    {
        return 'oro.channel.aggregate_lifetime_average';
    }

    #[\Override]
    public static function getDescription(): string
    {
        return 'Aggregates an average lifetime value.';
    }

    #[\Override]
    public function getDefaultPriority(string $queueName): string
    {
        return MessagePriority::VERY_LOW;
    }

    #[\Override]
    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined('force')
            ->setDefault('force', false)
            ->addAllowedTypes('force', 'bool');

        $resolver
            ->setDefined('use_truncate')
            ->setDefault('use_truncate', true)
            ->addAllowedTypes('use_truncate', 'bool');
    }

    #[\Override]
    public function createJobName($messageBody): string
    {
        return 'oro_channel:aggregate_lifetime_average';
    }
}
