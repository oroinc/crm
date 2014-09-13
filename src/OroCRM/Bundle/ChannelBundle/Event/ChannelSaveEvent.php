<?php

namespace OroCRM\Bundle\ChannelBundle\Event;

/**
 * This event dispatched each time when channel form is submitted
 * Note: it does not guarantee that channel was really updated.
 * Also need to keep in mind that if you implement channel save not thorough the form, you have to dispatch this event.
 */
class ChannelSaveEvent extends AbstractEvent
{
    const EVENT_NAME = 'orocrm_channel.channel.save_succeed';
}
