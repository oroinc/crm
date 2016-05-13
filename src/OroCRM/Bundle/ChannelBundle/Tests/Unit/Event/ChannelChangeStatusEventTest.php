<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Event;

use OroCRM\Bundle\ChannelBundle\Event\ChannelChangeStatusEvent;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChannelChangeStatusEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetter()
    {
        $channel = new Channel();
        $event   = new ChannelChangeStatusEvent($channel);

        $this->assertSame($channel, $event->getChannel());
    }
}
