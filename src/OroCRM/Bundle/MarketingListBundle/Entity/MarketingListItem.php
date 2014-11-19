<?php

namespace OroCRM\Bundle\MarketingListBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * Marketing list item.
 *
 * @ORM\Table(name="orocrm_marketing_list_item",  uniqueConstraints={
 *      @ORM\UniqueConstraint(columns={"entity_id", "marketing_list_id"}, name="orocrm_ml_list_ent_unq")
 * })
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @Config()
 */
class MarketingListItem
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
     * @var string
     *
     * @ORM\Column(name="entity_id", type="integer", nullable=false)
     */
    protected $entityId;

    /**
     * @var int
     * @ORM\Column(name="contacted_times", type="integer", nullable=true)
     */
    protected $contactedTimes;

    /**
     * @var MarketingList
     *
     * @ORM\ManyToOne(
     *      targetEntity="OroCRM\Bundle\MarketingListBundle\Entity\MarketingList", inversedBy="marketingListItems"
     * )
     * @ORM\JoinColumn(name="marketing_list_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $marketingList;

    /**
     * @var \DateTime
     * @ORM\Column(name="last_contacted_at", type="datetime", nullable=true)
     */
    protected $lastContactedAt;

    /**
     * @var \Datetime
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
     * @param int $entityId
     * @return MarketingListItem
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param MarketingList $marketingList
     * @return MarketingListItem
     */
    public function setMarketingList(MarketingList $marketingList)
    {
        $this->marketingList = $marketingList;

        return $this;
    }

    /**
     * @return MarketingList
     */
    public function getMarketingList()
    {
        return $this->marketingList;
    }

    /**
     * @param \Datetime $createdAt
     * @return MarketingListItem
     */
    public function setCreatedAt(\Datetime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return \DateTime
     */
    public function getLastContactedAt()
    {
        return $this->lastContactedAt;
    }

    /**
     * @param \DateTime $lastContactedAt
     * @return MarketingListItem
     */
    public function setLastContactedAt($lastContactedAt)
    {
        $this->lastContactedAt = $lastContactedAt;

        return $this;
    }

    /**
     * @return int
     */
    public function getContactedTimes()
    {
        return $this->contactedTimes;
    }

    /**
     * @param int $contactedTimes
     * @return MarketingListItem
     */
    public function setContactedTimes($contactedTimes)
    {
        $this->contactedTimes = $contactedTimes;

        return $this;
    }

    /**
     * Update contact activity.
     */
    public function contact()
    {
        $this->setLastContactedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $this->setContactedTimes((int)$this->getContactedTimes() + 1);
    }
}
