<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Event;

use OroCRM\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChannelSaveEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \TypeError
     */
    public function testConstructorRequiresChannel()
    {
        new ChannelSaveEvent(null);
    }

    public function testGetter()
    {
        $channel = new Channel();
        $event   = new ChannelSaveEvent($channel);

        $this->assertSame($channel, $event->getChannel());
    }
}
