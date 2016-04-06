<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Event;

use OroCRM\Bundle\ChannelBundle\Event\ChannelDeleteEvent;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChannelDeleteEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \TypeError
     */
    public function testConstructorRequiresChannel()
    {
        new ChannelDeleteEvent(null);
    }

    public function testGetter()
    {
        $channel = new Channel();
        $event   = new ChannelDeleteEvent($channel);

        $this->assertSame($channel, $event->getChannel());
    }
}
