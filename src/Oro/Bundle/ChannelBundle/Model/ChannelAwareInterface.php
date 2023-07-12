<?php

namespace Oro\Bundle\ChannelBundle\Model;

use Oro\Bundle\ChannelBundle\Entity\Channel;

interface ChannelAwareInterface
{
    /**
     * @TODO remove null after BAP-5248
     */
    public function setDataChannel(Channel $channel = null);

    /**
     * @return Channel
     */
    public function getDataChannel();
}
