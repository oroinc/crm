<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
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
    protected $name;

    /**
     * @var Channel
     *
     * @ORM\ManyToOne(targetEntity="Channel", inversedBy="entities")
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

    /**
     * @param Channel $channel
     */
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
}
