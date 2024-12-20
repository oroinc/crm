<?php

namespace Oro\Bundle\ChannelBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic to change channel data source
 */
class ChannelStatusChangedTopic extends AbstractTopic
{
    #[\Override]
    public static function getName(): string
    {
        return 'oro.channel.channel_status_changed';
    }

    #[\Override]
    public static function getDescription(): string
    {
        return 'Changes channel data source.';
    }

    #[\Override]
    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('channelId')
            ->addAllowedTypes('channelId', 'int');
    }
}
