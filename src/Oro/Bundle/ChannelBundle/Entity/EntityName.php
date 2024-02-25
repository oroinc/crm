<?php

namespace Oro\Bundle\ChannelBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
* Entity that represents Entity Name
*
*/
#[ORM\Entity]
#[ORM\Table(name: 'orocrm_channel_entity_name')]
class EntityName
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255, nullable: false)]
    protected ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Channel::class, inversedBy: 'entities')]
    #[ORM\JoinColumn(name: 'channel_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?Channel $channel = null;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setChannel(Channel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getName();
    }
}
