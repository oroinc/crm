<?php

namespace OroCRM\Bundle\MarketingListBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Marketing list removed items.
 *
 * @ORM\Table(name="orocrm_ml_item_rm",  uniqueConstraints={
 *      @ORM\UniqueConstraint(columns={"entity_id", "marketing_list_id"}, name="orocrm_ml_list_ent_rm_unq")
 * })
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class MarketingListRemovedItem implements MarketingListStateItemInterface
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
     * @var MarketingList
     *
     * @ORM\ManyToOne(
     *     targetEntity="OroCRM\Bundle\MarketingListBundle\Entity\MarketingList", inversedBy="marketingListRemovedItems"
     * )
     * @ORM\JoinColumn(name="marketing_list_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $marketingList;

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
     * {@inheritdoc}
     *
     * @return MarketingListRemovedItem
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * {@inheritdoc}
     *
     * @return MarketingListRemovedItem
     */
    public function setMarketingList(MarketingList $marketingList)
    {
        $this->marketingList = $marketingList;

        return $this;
    }

    /**
     * {@inheritdoc}
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
}
