<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Event;

use Oro\Bundle\ChannelBundle\Event\ChannelChangeStatusEvent;
use Oro\Bundle\ChannelBundle\Entity\Channel;

class ChannelChangeStatusEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetter()
    {
        $channel = new Channel();
        $event   = new ChannelChangeStatusEvent($channel);

        $this->assertSame($channel, $event->getChannel());
    }
}
