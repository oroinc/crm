<?php

namespace OroCRM\Bundle\ChannelBundle\Model;

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
     *          "short"=true,
     *          "order"=5
     *      }
     *  }
     * )
     */
    protected $dataChannel;

    /**
     * {@inheritdoc}
     * @TODO remove null after BAP-5248
     */
    public function setDataChannel(Channel $channel = null)
    {
        $this->dataChannel = $channel;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataChannel()
    {
        return $this->dataChannel;
    }
}
