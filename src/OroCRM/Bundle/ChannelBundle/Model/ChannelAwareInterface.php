<?php

namespace OroCRM\Bundle\ChannelBundle\Model;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

interface ChannelAwareInterface
{
    /**
     * @param Channel $channel
     */
    public function setDataChannel(Channel $channel);

    /**
     * @return Channel
     */
    public function getDataChannel();
}
