<?php
namespace Oro\Bundle\ChannelBundle\Tests\Functional\EventListener;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\ChannelBundle\EventListener\TimezoneChangeListener;

class TimezoneChangeListenerTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initClient();
    }

    public function testCouldBeGetFromContainerAsService()
    {
        $service = $this->getContainer()->get('oro_channel.event_listener.timezone_change');

        $this->assertInstanceOf(TimezoneChangeListener::class, $service);
    }
}
