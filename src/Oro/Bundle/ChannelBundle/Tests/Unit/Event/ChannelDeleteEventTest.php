<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Event;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelDeleteEvent;

class ChannelDeleteEventTest extends \PHPUnit\Framework\TestCase
{
    public function testGetter()
    {
        $channel = new Channel();
        $event   = new ChannelDeleteEvent($channel);

        $this->assertSame($channel, $event->getChannel());
    }
}
