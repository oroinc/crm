<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Event;

use Oro\Bundle\ChannelBundle\Event\ChannelDeleteEvent;
use Oro\Bundle\ChannelBundle\Entity\Channel;

class ChannelDeleteEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetter()
    {
        $channel = new Channel();
        $event   = new ChannelDeleteEvent($channel);

        $this->assertSame($channel, $event->getChannel());
    }
}
