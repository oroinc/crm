<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Unit\Model\Stub;

use OroCRM\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class CustomerAwareStub implements AnalyticsAwareInterface
{
    public function setDataChannel(Channel $channel = null)
    {
    }

    public function getDataChannel()
    {
    }
}
