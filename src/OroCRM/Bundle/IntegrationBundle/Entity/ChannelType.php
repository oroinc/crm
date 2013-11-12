<?php

namespace OroCRM\Bundle\IntegrationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use OroCRM\Bundle\IntegrationBundle\Provider\ChannelTypeInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="orocrm_channel_type")
 */
class ChannelType implements ChannelTypeInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="smallint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(type="text")
     */
    protected $settings;

    /** Init channel type */
    public function init()
    {
        // TODO: Implement init() method.
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return mixed
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
