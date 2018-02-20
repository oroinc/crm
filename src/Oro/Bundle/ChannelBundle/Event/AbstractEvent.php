<?php

namespace Oro\Bundle\ChannelBundle\Event;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Symfony\Component\EventDispatcher\Event;

class AbstractEvent extends Event
{
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
