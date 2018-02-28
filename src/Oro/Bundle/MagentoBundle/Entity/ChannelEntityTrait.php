<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

trait ChannelEntityTrait
{
    /**
     * @var Channel
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\ChannelBundle\Entity\Channel")
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
