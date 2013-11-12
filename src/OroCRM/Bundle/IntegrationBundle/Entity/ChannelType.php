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

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * @return $this
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSettings()
    {
        if (is_string($this->settings)) {
            $this->settings = json_decode($this->settings, true);
        }

        return $this->settings;
    }
}
