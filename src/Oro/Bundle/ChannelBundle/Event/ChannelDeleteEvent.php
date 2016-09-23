<?php

namespace Oro\Bundle\ChannelBundle\Event;

/**
 * This event dispatched when channel is deleted
 * Keep in mind that if you implement channel delete not thorough the API, you have to dispatch this event.
 */
class ChannelDeleteEvent extends AbstractEvent
{
    const EVENT_NAME = 'oro_channel.channel.delete_succeed';
}
