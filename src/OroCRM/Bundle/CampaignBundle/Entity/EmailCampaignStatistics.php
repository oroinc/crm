<?php

namespace OroCRM\Bundle\CampaignBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem;

/**
 * Email Campaign Statistics.
 *
 * @ORM\Table(name="orocrm_campaign_email_stats")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class EmailCampaignStatistics
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
