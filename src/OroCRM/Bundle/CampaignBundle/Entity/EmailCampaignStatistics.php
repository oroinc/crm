<?php

namespace OroCRM\Bundle\CampaignBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\CampaignBundle\Model\ExtendEmailCampaignStatistics;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem;

/**
 * Email Campaign Statistics.
 *
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\CampaignBundle\Entity\Repository\EmailCampaignStatisticsRepository")
 * @ORM\Table(name="orocrm_campaign_email_stats", uniqueConstraints={
 *      @ORM\UniqueConstraint(columns={"email_campaign_id", "marketing_list_item_id"}, name="orocrm_ec_litem_unq")
 * })
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-bar-chart"
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
 *              "group_name"="",
 *              "category"="marketing"
 *          }
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
class EmailCampaignStatistics extends ExtendEmailCampaignStatistics
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var MarketingListItem
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem")
     * @ORM\JoinColumn(name="marketing_list_item_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $marketingListItem;

    /**
     * @var EmailCampaign
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign")
     * @ORM\JoinColumn(name="email_campaign_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $emailCampaign;

    /**
     * @var int
     *
     * @ORM\Column(name="open_count", type="integer", nullable=true)
     */
    protected $openCount;

    /**
     * @var int
     *
     * @ORM\Column(name="click_count", type="integer", nullable=true)
     */
    protected $clickCount;

    /**
     * @var int
     *
     * @ORM\Column(name="bounce_count", type="integer", nullable=true)
     */
    protected $bounceCount;

    /**
     * @var int
     *
     * @ORM\Column(name="abuse_count", type="integer", nullable=true)
     */
    protected $abuseCount;

    /**
     * @var int
     *
     * @ORM\Column(name="unsubscribe_count", type="integer", nullable=true)
     */
    protected $unsubscribeCount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return MarketingListItem
     */
    public function getMarketingListItem()
    {
        return $this->marketingListItem;
    }

    /**
     * @param MarketingListItem $marketingListItem
     * @return EmailCampaignStatistics
     */
    public function setMarketingListItem(MarketingListItem $marketingListItem)
    {
        $this->marketingListItem = $marketingListItem;

        return $this;
    }

    /**
     * @return EmailCampaign
     */
    public function getEmailCampaign()
    {
        return $this->emailCampaign;
    }

    /**
     * @param EmailCampaign $emailCampaign
     * @return EmailCampaignStatistics
     */
    public function setEmailCampaign(EmailCampaign $emailCampaign)
    {
        $this->emailCampaign = $emailCampaign;

        return $this;
    }

    /**
     * @return int
     */
    public function getOpenCount()
    {
        return $this->openCount;
    }

    /**
     * @param int $openCount
     * @return EmailCampaignStatistics
     */
    public function setOpenCount($openCount)
    {
        $this->openCount = $openCount;

        return $this;
    }

    /**
     * @return EmailCampaignStatistics
     */
    public function incrementOpenCount()
    {
        $this->openCount++;

        return $this;
    }

    /**
     * @return int
     */
    public function getClickCount()
    {
        return $this->clickCount;
    }

    /**
     * @param int $clickCount
     * @return EmailCampaignStatistics
     */
    public function setClickCount($clickCount)
    {
        $this->clickCount = $clickCount;

        return $this;
    }

    /**
     * @return EmailCampaignStatistics
     */
    public function incrementClickCount()
    {
        $this->clickCount++;

        return $this;
    }

    /**
     * @return int
     */
    public function getBounceCount()
    {
        return $this->bounceCount;
    }

    /**
     * @param int $bounceCount
     * @return EmailCampaignStatistics
     */
    public function setBounceCount($bounceCount)
    {
        $this->bounceCount = $bounceCount;

        return $this;
    }

    /**
     * @return EmailCampaignStatistics
     */
    public function incrementBounceCount()
    {
        $this->bounceCount++;

        return $this;
    }

    /**
     * @return int
     */
    public function getAbuseCount()
    {
        return $this->abuseCount;
    }

    /**
     * @param int $abuseCount
     * @return EmailCampaignStatistics
     */
    public function setAbuseCount($abuseCount)
    {
        $this->abuseCount = $abuseCount;

        return $this;
    }

    /**
     * @return EmailCampaignStatistics
     */
    public function incrementAbuseCount()
    {
        $this->abuseCount++;

        return $this;
    }

    /**
     * @return int
     */
    public function getUnsubscribeCount()
    {
        return $this->unsubscribeCount;
    }

    /**
     * @param int $unsubscribeCount
     * @return EmailCampaignStatistics
     */
    public function setUnsubscribeCount($unsubscribeCount)
    {
        $this->unsubscribeCount = $unsubscribeCount;

        return $this;
    }

    /**
     * @return EmailCampaignStatistics
     */
    public function incrementUnsubscribeCount()
    {
        $this->unsubscribeCount++;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return EmailCampaignStatistics
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Pre persist event handler
     *
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Set owner
     *
     * @param User $owner
     *
     * @return EmailCampaignStatistics
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
     * Set organization
     *
     * @param Organization $organization
     * @return EmailCampaignStatistics
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
        return (string)$this->getId();
    }
}
