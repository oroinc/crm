<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Unit\Form\Extension\Stub;

use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareTrait;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class RFMAwareStub implements RFMAwareInterface
{
    use RFMAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function setDataChannel(Channel $channel = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDataChannel()
    {
    }
}
