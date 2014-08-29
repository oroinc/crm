<?php

namespace OroCRM\Bundle\ChannelBundle\Model;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

trait ChannelEntityTrait
{
    /**
     * @var Channel
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ChannelBundle\Entity\Channel")
     * @ORM\JoinColumn(name="data_channel_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $dataChannel;

    /**
     * @param Channel $channel
     */
    public function setDataChannel(Channel $channel)
    {
        $this->dataChannel = $channel;
    }

    /**
     * @return Channel
     */
    public function getDataChannel()
    {
        return $this->dataChannel;
    }
}
