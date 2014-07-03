<?php

namespace OroCRM\Bundle\ChannelBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

/**
 * This event dispatched each time when channel form is submitted
 * Note: id does not guarantee that channel was really updated.
 * Also need to keep in mind that if you implement channel save not thorough the form, you have to dispatch this event.
 */
class ChannelSaveEvent extends Event
{
    const EVENT_NAME = 'orocrm_channel.channel.save_succeed';

    /** @var Channel */
    protected $channel;

    /**
     * @param Channel $channel
     */
    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
