<?php

namespace OroCRM\Bundle\AnalyticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

/**
 * @ORM\Entity
 * @ORM\Table(name="orocrm_analytics_rfm_category")
 * @Config(
 *  defaultValues={
 *      "entity"={
 *      },
 *      "ownership"={
 *          "owner_type"="ORGANIZATION",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class RFMMetricCategory
{
    const TYPE_RECENCY = 'recency';
    const TYPE_FREQUENCY = 'frequency';
    const TYPE_MONETARY = 'monetary';

    /**
     * @var array
     */
    public static $types = [
        self::TYPE_RECENCY,
        self::TYPE_FREQUENCY,
        self::TYPE_MONETARY
    ];

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="`type`", type="string", length=16, nullable=false)
     */
    protected $type;

    /**
     * @var int
     *
     * @ORM\Column(name="`index`", type="integer", nullable=false)
     */
    protected $index;

    /**
     * @var int
     *
     * @ORM\Column(name="min_value", type="integer", nullable=true)
     */
    protected $minValue;

    /**
     * @var int
     *
     * @ORM\Column(name="max_value", type="integer", nullable=true)
     */
    protected $maxValue;

    /**
     * @var Channel
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ChannelBundle\Entity\Channel")
     * @ORM\JoinColumn(name="channel_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $channel;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return RFMMetricCategory
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set index
     *
     * @param integer $index
     * @return RFMMetricCategory
     */
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Get index
     *
     * @return integer
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Set minValue
     *
     * @param integer $minValue
     * @return RFMMetricCategory
     */
    public function setMinValue($minValue)
    {
        $this->minValue = $minValue;

        return $this;
    }

    /**
     * Get minValue
     *
     * @return integer
     */
    public function getMinValue()
    {
        return $this->minValue;
    }

    /**
     * Set maxValue
     *
     * @param integer $maxValue
     * @return RFMMetricCategory
     */
    public function setMaxValue($maxValue)
    {
        $this->maxValue = $maxValue;

        return $this;
    }

    /**
     * Get maxValue
     *
     * @return integer 
     */
    public function getMaxValue()
    {
        return $this->maxValue;
    }

    /**
     * Set owner
     *
     * @param Organization $owner
     * @return RFMMetricCategory
     */
    public function setOwner($owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return Organization
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set channel
     *
     * @param Channel $channel
     * @return RFMMetricCategory
     */
    public function setChannel(Channel $channel = null)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Get channel
     *
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }
}
