<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Event;

use OroCRM\Bundle\ChannelBundle\Event\ChannelDeleteEvent;

class ChannelDeleteEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testConstructorRequiresChannel()
    {
        new ChannelDeleteEvent(null);
    }

    public function testGetter()
    {
        $channel = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $event   = new ChannelDeleteEvent($channel);

        $this->assertSame($channel, $event->getChannel());
    }
}
