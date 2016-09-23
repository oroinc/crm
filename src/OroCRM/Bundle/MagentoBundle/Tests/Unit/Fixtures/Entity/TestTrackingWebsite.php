<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Fixtures\Entity;

use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;
use Oro\Bundle\ChannelBundle\Entity\Channel;

class TestTrackingWebsite extends TrackingWebsite
{
    /** @var Channel */
    protected $channel;

    /**
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param Channel $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }
}
