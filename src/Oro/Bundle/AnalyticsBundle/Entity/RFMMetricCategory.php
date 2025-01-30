<?php

namespace Oro\Bundle\AnalyticsBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AnalyticsBundle\Entity\Repository\RFMMetricCategoryRepository;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
* Entity that represents R F M Metric Category
*
*/
#[ORM\Entity(repositoryClass: RFMMetricCategoryRepository::class)]
#[ORM\Table(name: 'orocrm_analytics_rfm_category')]
#[Config(
    defaultValues: [
        'ownership' => [
            'owner_type' => 'ORGANIZATION',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'owner_id'
        ],
        'security' => ['type' => 'ACL', 'group_name' => '', 'category' => 'account_management']
    ]
)]
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

    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Organization $owner = null;

    #[ORM\Column(name: 'category_type', type: Types::STRING, length: 16, nullable: false)]
    protected ?string $categoryType = null;

    #[ORM\Column(name: 'category_index', type: Types::INTEGER, nullable: false)]
    protected ?int $categoryIndex = null;

    /**
     * @return float|null
     */
    #[ORM\Column(name: 'min_value', type: Types::FLOAT, nullable: true)]
    protected $minValue;

    /**
     * @return float|null
     */
    #[ORM\Column(name: 'max_value', type: Types::FLOAT, nullable: true)]
    protected $maxValue;

    #[ORM\ManyToOne(targetEntity: Channel::class)]
    #[ORM\JoinColumn(name: 'channel_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Channel $channel = null;

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
     * @param Channel|null $channel
     * @return RFMMetricCategory
     */
    public function setChannel(?Channel $channel = null)
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
    #[\Override]
    public function __toString()
    {
        return (string)$this->getId();
    }
}
