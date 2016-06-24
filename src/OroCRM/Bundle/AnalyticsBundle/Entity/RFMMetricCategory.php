<?php

namespace OroCRM\Bundle\AnalyticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

/**
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\AnalyticsBundle\Entity\Repository\RFMMetricCategoryRepository")
 * @ORM\Table(name="orocrm_analytics_rfm_category")
 * @Config(
 *  defaultValues={
 *      "ownership"={
 *          "owner_type"="ORGANIZATION",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"="",
 *          "category"="account_management"
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
     * @ORM\Column(name="category_type", type="string", length=16, nullable=false)
     */
    protected $categoryType;

    /**
     * @var int
     *
     * @ORM\Column(name="category_index", type="integer", nullable=false)
     */
    protected $categoryIndex;

    /**
     * @var float
     *
     * @ORM\Column(name="min_value", type="float", nullable=true)
     */
    protected $minValue;

    /**
     * @var float
     *
     * @ORM\Column(name="max_value", type="float", nullable=true)
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
     * Set category type
     *
     * @param string $categoryType
     * @return RFMMetricCategory
     */
    public function setCategoryType($categoryType)
    {
        $this->categoryType = $categoryType;

        return $this;
    }

    /**
     * Get category type
     *
     * @return string
     */
    public function getCategoryType()
    {
        return $this->categoryType;
    }

    /**
     * Set category index
     *
     * @param integer $categoryIndex
     * @return RFMMetricCategory
     */
    public function setCategoryIndex($categoryIndex)
    {
        $this->categoryIndex = (int)$categoryIndex;

        return $this;
    }

    /**
     * Get category index
     *
     * @return integer
     */
    public function getCategoryIndex()
    {
        return $this->categoryIndex;
    }

    /**
     * Set minValue
     *
     * @param float $minValue
     * @return RFMMetricCategory
     */
    public function setMinValue($minValue)
    {
        if (!is_null($minValue)) {
            $minValue = (float)$minValue;
        }

        $this->minValue = $minValue;

        return $this;
    }

    /**
     * Get minValue
     *
     * @return float
     */
    public function getMinValue()
    {
        return $this->minValue;
    }

    /**
     * Set maxValue
     *
     * @param float $maxValue
     * @return RFMMetricCategory
     */
    public function setMaxValue($maxValue)
    {
        if (!is_null($maxValue)) {
            $maxValue = (float)$maxValue;
        }

        $this->maxValue = $maxValue;

        return $this;
    }

    /**
     * Get maxValue
     *
     * @return float
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

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }
}
