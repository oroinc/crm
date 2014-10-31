<?php

namespace OroCRM\Bundle\CampaignBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use OroCRM\Bundle\CampaignBundle\Model\ExtendEmailCampaignStatistics;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem;

/**
 * Email Campaign Statistics.
 *
 * @ORM\Table(name="orocrm_campaign_email_stats", uniqueConstraints={
 *      @ORM\UniqueConstraint(columns={"email_campaign_id", "marketing_list_item_id"}, name="orocrm_ec_litem_unq")
 * })
 * @ORM\Entity
 * @Config()
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
}
