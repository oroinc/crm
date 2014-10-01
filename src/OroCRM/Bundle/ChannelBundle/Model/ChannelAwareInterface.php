<?php

namespace OroCRM\Bundle\ChannelBundle\Model;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

interface ChannelAwareInterface
{
    /**
     * @param Channel $channel
     *
     * @TODO remove null after BAP-5248
     */
    public function setDataChannel(Channel $channel = null);

    /**
     * @return Channel
     */
    public function getDataChannel();
}
