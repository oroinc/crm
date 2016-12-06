<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Model\Stub;

use Oro\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface;
use Oro\Bundle\ChannelBundle\Entity\Channel;

class CustomerAwareStub implements AnalyticsAwareInterface
{
    public function setDataChannel(Channel $channel = null)
    {
    }

    public function getDataChannel()
    {
    }
}
