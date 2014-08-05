<?php

namespace OroCRM\Bundle\ChannelBundle\Event;

/**
 * This event dispatched when channel status changed
 */
class ChannelChangeStatusEvent extends AbstractEvent
{
    const EVENT_NAME = 'orocrm_channel.channel.status_change';
}
