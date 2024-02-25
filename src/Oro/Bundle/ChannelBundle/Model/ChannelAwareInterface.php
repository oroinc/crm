<?php

namespace Oro\Bundle\ChannelBundle\Model;

use Oro\Bundle\ChannelBundle\Entity\Channel;

/**
 * ChannelAware interface declares methods to define the source of customer data
 *
 */
interface ChannelAwareInterface
{
    /**
     * Remove null after BAP-5248
     */
    public function setDataChannel(Channel $channel = null);

    /**
     * @return Channel
     */
    public function getDataChannel();
}
