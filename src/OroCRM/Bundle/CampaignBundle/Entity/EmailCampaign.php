<?php

namespace OroCRM\Bundle\CampaignBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\CampaignBundle\Model\ExtendEmailCampaign;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

/**
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\CampaignBundle\Entity\Repository\EmailCampaignRepository")
 * @ORM\Table(
 *      name="orocrm_campaign_email",
 *      indexes={@ORM\Index(name="cmpgn_email_owner_idx", columns={"owner_id"})}
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-envelope",
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
 *          "grid"={
 *              "default"="orocrm-email-campaign-grid"
 *          },
 *          "tag"={
 *              "enabled"=true
 *          }
 *      }
 * )
 */
class EmailCampaign extends ExtendEmailCampaign
{
    const SCHEDULE_MANUAL = 'manual';
    const SCHEDULE_DEFERRED = 'deferred';

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
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_sent", type="boolean")
     */
    protected $sent = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sent_at", type="datetime", nullable=true)
     */
    protected $sentAt;

    /**
     * @var string
     *
     * @ORM\Column(name="schedule", type="string", length=255)
     */
    protected $schedule;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="scheduled_for", type="datetime", nullable=true)
     */
    protected $scheduledFor;

    /**
     * @var string
     *
     * @ORM\Column(name="sender_email", type="string", length=255, nullable=true)
     */
    protected $senderEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="sender_name", type="string", length=255, nullable=true)
     */
    protected $senderName;

    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Campaign")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $campaign;

    /**
     * @var MarketingList
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MarketingListBundle\Entity\MarketingList")
     * @ORM\JoinColumn(name="marketing_list_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $marketingList;

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
     * @ORM\Column(name="transport", type="string", length=255, nullable=false)
     */
    protected $transport;

    /**
     * @var TransportSettings
     *
     * @ORM\OneToOne(
     *     targetEntity="OroCRM\Bundle\CampaignBundle\Entity\TransportSettings",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="transport_settings_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $transportSettings;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

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
     * Pre persist event handler
     *
     * @ORM\PrePersist
     */
    public function prePersist()
    {
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
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return null|string
     */
    public function getEntityName()
    {
        if ($this->marketingList) {
            return $this->marketingList->getEntity();
        }

        return null;
    }

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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return EmailCampaign
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return EmailCampaign
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set campaign
     *
     * @param Campaign $campaign
     *
     * @return EmailCampaign
     */
    public function setCampaign(Campaign $campaign = null)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * Get campaign
     *
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * Set marketingList
     *
     * @param MarketingList $marketingList
     *
     * @return EmailCampaign
     */
    public function setMarketingList(MarketingList $marketingList)
    {
        $this->marketingList = $marketingList;

        return $this;
    }

    /**
     * Get marketingList
     *
     * @return MarketingList
     */
    public function getMarketingList()
    {
        return $this->marketingList;
    }

    /**
     * Set owner
     *
     * @param User $owner
     *
     * @return EmailCampaign
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return EmailCampaign
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return EmailCampaign
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set sent
     *
     * @param boolean $sent
     * @return EmailCampaign
     */
    public function setSent($sent)
    {
        $this->sent = $sent;
        $this->sentAt = new \DateTime('now', new \DateTimeZone('UTC'));

        return $this;
    }

    /**
     * Get isSent
     *
     * @return boolean
     */
    public function isSent()
    {
        return $this->sent;
    }

    /**
     * Set schedule
     *
     * @param string $schedule
     * @return EmailCampaign
     */
    public function setSchedule($schedule)
    {
        $types = [self::SCHEDULE_MANUAL, self::SCHEDULE_DEFERRED];

        if (!in_array($schedule, $types)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Schedule type %s is not know. Known types are %s',
                    $schedule,
                    implode(', ', $types)
                )
            );
        }
        $this->schedule = $schedule;

        return $this;
    }

    /**
     * Get schedule
     *
     * @return string
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * @return \DateTime
     */
    public function getScheduledFor()
    {
        return $this->scheduledFor;
    }

    /**
     * @param \DateTime $scheduledFor
     * @return EmailCampaign
     */
    public function setScheduledFor($scheduledFor)
    {
        $this->scheduledFor = $scheduledFor;

        return $this;
    }

    /**
     * Set Sender Email address.
     *
     * @param string $senderEmail
     * @return EmailCampaign
     */
    public function setSenderEmail($senderEmail)
    {
        $this->senderEmail = $senderEmail;

        return $this;
    }

    /**
     * Get Sender Email address.
     *
     * @return string
     */
    public function getSenderEmail()
    {
        return $this->senderEmail;
    }

    /**
     * Set Sender Name.
     *
     * @param string $senderName
     * @return EmailCampaign
     */
    public function setSenderName($senderName)
    {
        $this->senderName = $senderName;

        return $this;
    }

    /**
     * Get Sender Name.
     *
     * @return string
     */
    public function getSenderName()
    {
        return $this->senderName;
    }

    /**
     * Set sentAt
     *
     * @param \DateTime $sentAt
     * @return EmailCampaign
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * Get sentAt
     *
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * @return string
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @param string $transport
     * @return EmailCampaign
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;

        return $this;
    }

    /**
     * @return TransportSettings
     */
    public function getTransportSettings()
    {
        return $this->transportSettings;
    }

    /**
     * @param TransportSettings $transportSettings
     * @return EmailCampaign
     */
    public function setTransportSettings($transportSettings)
    {
        $this->transportSettings = $transportSettings;

        return $this;
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     * @return EmailCampaign
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
}
