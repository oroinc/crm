<?php

namespace Oro\Bundle\ChannelBundle\Event;

class ChannelBeforeDeleteEvent extends AbstractEvent
{
    public const EVENT_NAME = 'oro_channel.channel.before_delete';
}
