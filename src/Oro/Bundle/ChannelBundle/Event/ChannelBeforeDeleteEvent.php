<?php

namespace Oro\Bundle\ChannelBundle\Event;

/**
 * Dispatched before a channel is deleted, allowing listeners to perform cleanup of related data and integrations.
 */
class ChannelBeforeDeleteEvent extends AbstractEvent
{
    const EVENT_NAME = 'oro_channel.channel.before_delete';
}
