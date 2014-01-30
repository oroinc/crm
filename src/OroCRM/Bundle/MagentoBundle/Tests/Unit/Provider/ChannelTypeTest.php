<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;

class ChannelTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChannelType */
    protected $channel;

    public function setUp()
    {
        $this->channel = new ChannelType();
    }

    public function tearDown()
    {
        unset($this->channel);
    }

    public function testPublicInterface()
    {
        $this->assertEquals('orocrm.magento.channel_type.label', $this->channel->getLabel());
    }
}
