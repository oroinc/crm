<?php

namespace Oro\Bundle\ChannelBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;

/**
 * Traits that implements {@see \Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface}
 */
trait ChannelEntityTrait
{
    /**
     * @var Channel
     */
    #[ORM\ManyToOne(targetEntity: 'Oro\Bundle\ChannelBundle\Entity\Channel')]
    #[ORM\JoinColumn(name: 'data_channel_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['importexport' => ['short' => true, 'order' => 5]])]
    protected $dataChannel;

    public function setDataChannel(?Channel $channel = null)
    {
        $this->dataChannel = $channel;
    }

    public function getDataChannel()
    {
        return $this->dataChannel;
    }
}
