<?php

namespace OroCRM\Bundle\CampaignBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\CampaignBundle\Model\ExtendCampaign;

/**
 * @package OroCRM\Bundle\OroCRMCampaignBundle\Entity
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\CampaignBundle\Entity\Repository\CampaignRepository")
 * @ORM\Table(
 *      name="orocrm_campaign",
 *      indexes={@ORM\Index(name="cmpgn_owner_idx", columns={"owner_id"})}
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-volume-up",
 *              "category"="campaign"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "form"={
 *              "form_type"="orocrm_campaign_select",
 *              "grid_name"="orocrm-campaign-grid",
 *          },
 *          "grid"={
 *              "default"="orocrm-campaign-grid"
 *          },
 *          "tag"={
 *              "enabled"=true
 *          }
 *      }
 * )
 */
class Campaign extends ExtendCampaign
{
    const PERIOD_HOURLY  = 'hour';
    const PERIOD_DAILY   = 'day';
    const PERIOD_MONTHLY = 'month';
    const PERIOD_YEARLY  = 'year';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    protected $code;

    /**
     * This field needed as label in related entities drown select
     *
     * @var string
     *
     * @ORM\Column(name="combined_name", type="string", length=255, nullable=true)
     */
    protected $combinedName;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="start_date", type="date", nullable=true)
     */
    protected $startDate;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="end_date", type="date", nullable=true)
     */
    protected $endDate;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var double
     *
     * @ORM\Column(name="budget", type="money", nullable=true)
     */
    protected $budget;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="report_period", type="string", length=25)
     */
    protected $reportPeriod;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="report_refresh_date", type="date", nullable=true)
     */
    protected $reportRefreshDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->reportPeriod = self::PERIOD_DAILY;
    }

    /**
     * @return mixed
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
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param float $budget
     */
    public function setBudget($budget)
    {
        $this->budget = $budget;
    }

    /**
     * @return float
     */
    public function getBudget()
    {
        return $this->budget;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Get campaign created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get campaign last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Pre persist event handler
     *
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setCombinedName($this->name, $this->code);
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
    }

    /**
     * Pre update event handler
     *
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->setCombinedName($this->name, $this->code);
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Set combined name in format "campaign name (campaign_code)"
     *
     * @param string $name
     * @param string $code
     */
    public function setCombinedName($name, $code)
    {
        $this->combinedName = sprintf('%s (%s)', $name, $code);
    }

    /**
     * @return string
     */
    public function getCombinedName()
    {
        return $this->combinedName;
    }

    /**
     *  Get report period.
     *
     * @return string
     */
    public function getReportPeriod()
    {
        return $this->reportPeriod;
    }

    /**
     * Set report period.
     *
     * @param string $reportPeriod
     * @return Campaign
     */
    public function setReportPeriod($reportPeriod)
    {
        $this->reportPeriod = $reportPeriod;

        return $this;
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     * @return Campaign
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getName();
    }

    /**
     * @return \DateTime
     */
    public function getReportRefreshDate()
    {
        return $this->reportRefreshDate;
    }

    /**
     * @param \DateTime $reportRefreshDate
     * @return Campaign
     */
    public function setReportRefreshDate($reportRefreshDate)
    {
        $this->reportRefreshDate = $reportRefreshDate;

        return $this;
    }
}
