<?php

namespace OroCRM\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\EmailBundle\Entity\MailboxProcessorSettings;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

/**
 * @ORM\Entity
 * @Config
 */
class LeadMailboxProcessorSettings extends MailboxProcessorSettings
{
    const TYPE = 'lead';

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="lead_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var Channel
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ChannelBundle\Entity\Channel")
     * @ORM\JoinColumn(name="lead_channel_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $channel;

    /**
     * @var string
     *
     * @ORM\Column(name="lead_source_id", type="string")
     */
    protected $source;

    /** @var ParameterBag */
    private $settings;

    /**
     * {@inheritdoc}
     */
    public function getSettings()
    {
        if ($this->settings === null) {
            return $this->settings = new ParameterBag([
                'owner'   => $this->getOwner(),
                'channel' => $this->getChannel(),
                'source'  => $this->getSource(), // This field is added through entity extension.
            ]);
        }

        return $this->settings;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     *
     * @return $this
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param mixed $channel
     *
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     *
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }
}
