<?php

namespace Oro\Bridge\MarketingCRM\Tests\Unit\Fixtures\Entity;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;

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
