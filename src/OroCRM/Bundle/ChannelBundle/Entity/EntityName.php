<?php

namespace OroCRM\Bundle\ChannelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="orocrm_channel_entity_name")
 */
class EntityName
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $value;

    /**
     * @var Channel
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ChannelBundle\Entity\Channel", inversedBy="entities")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $channel;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
