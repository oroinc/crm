<?php

namespace Oro\Bundle\ChannelBundle\Event;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Base event class for channel-related events, providing access to the channel entity being affected.
 */
class AbstractEvent extends Event
{
    /** @var Channel */
    protected $channel;

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
