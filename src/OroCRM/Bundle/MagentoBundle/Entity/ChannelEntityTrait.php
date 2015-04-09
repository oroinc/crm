<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

trait ChannelEntityTrait
{
    /**
     * @var Channel
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ChannelBundle\Entity\Channel")
     * @ORM\JoinColumn(name="data_channel_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *  defaultValues={
     *      "importexport"={
     *          "excluded"=true
     *      }
     *  }
     * )
     */
    protected $dataChannel;

    /**
     * @param Channel|null $channel
     * @return self
     *
     * @TODO remove null after BAP-5248
     */
    public function setDataChannel(Channel $channel = null)
    {
        $this->dataChannel = $channel;

        return $this;
    }

    /**
     * @return Channel
     */
    public function getDataChannel()
    {
        return $this->dataChannel;
    }
}
