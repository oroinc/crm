<?php

namespace OroCRM\Bundle\MarketingListBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use OroCRM\Bundle\MarketingListBundle\Model\ExtendMarketingList;

/**
 * Marketing list
 *
 * @ORM\Table(name="orocrm_marketing_list")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 * @Config(
 *      routeName="orocrm_marketing_list_index",
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-list-alt",
 *              "category"="marketing"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "form"={
 *              "form_type"="orocrm_marketing_list_select",
 *              "grid_name"="orocrm-marketing-list-grid",
 *          },
 *          "grid"={
 *              "default"="orocrm-marketing-list-grid"
 *          },
 *          "tag"={
 *              "enabled"=true
 *          }
 *      }
 * )
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class MarketingList extends ExtendMarketingList
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true, length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="entity", type="string", unique=false, length=255, nullable=false)
     */
    protected $entity;

    /**
     * @var MarketingListType
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType")
     * @ORM\JoinColumn(name="type", referencedColumnName="name", nullable=false)
     **/
    protected $type;

    /**
     * @var Segment
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\SegmentBundle\Entity\Segment", cascade={"all"})
     * @ORM\JoinColumn(name="segment_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     **/
    protected $segment;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(
     *      targetEntity="MarketingListItem", mappedBy="marketingList",
     *      cascade={"all"}, orphanRemoval=true
     * )
     */
    protected $marketingListItems;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(
     *      targetEntity="MarketingListUnsubscribedItem", mappedBy="marketingList",
     *      cascade={"all"}, orphanRemoval=true
     * )
     */
    protected $marketingListUnsubscribedItems;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(
     *      targetEntity="MarketingListRemovedItem", mappedBy="marketingList",
     *      cascade={"all"}, orphanRemoval=true
     * )
     */
    protected $marketingListRemovedItems;

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
     * @var \Datetime
     *
     * @ORM\Column(name="last_run", type="datetime", nullable=true)
     */
    protected $lastRun;

    /**
     * @var \Datetime
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
     * @var \Datetime
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

    public function __construct()
    {
        parent::__construct();

        $this->marketingListItems = new ArrayCollection();
        $this->marketingListRemovedItems = new ArrayCollection();
        $this->marketingListUnsubscribedItems = new ArrayCollection();
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return MarketingList
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return MarketingList
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get marketing list type
     *
     * @return MarketingListType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set marketing list type
     *
     * @param MarketingListType $type
     * @return MarketingList
     */
    public function setType(MarketingListType $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isManual()
    {
        if ($this->type) {
            return $this->type->getName() === MarketingListType::TYPE_MANUAL;
        }

        return false;
    }

    /**
     * @return Segment
     */
    public function getSegment()
    {
        return $this->segment;
    }

    /**
     * @param Segment $segment
     * @return MarketingList
     */
    public function setSegment($segment)
    {
        $this->segment = $segment;

        return $this;
    }

    /**
     * Get the full name of an entity on which this marketing list is based
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the full name of an entity on which this marketing list is based
     *
     * @param string $entity
     * @return MarketingList
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get owner user.
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set a user owning this marketing list
     *
     * @param User $owning
     * @return MarketingList
     */
    public function setOwner(User $owning)
    {
        $this->owner = $owning;

        return $this;
    }

    /**
     * Get created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set created date/time
     *
     * @param \DateTime $created
     * @return MarketingList
     */
    public function setCreatedAt(\DateTime $created)
    {
        $this->createdAt = $created;

        return $this;
    }

    /**
     * Get last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set last update date/time
     *
     * @param \DateTime $updated
     * @return MarketingList
     */
    public function setUpdatedAt(\DateTime $updated)
    {
        $this->updatedAt = $updated;

        return $this;
    }

    /**
     * Set last run date/time
     *
     * @param \Datetime $lastRun
     * @return MarketingList
     */
    public function setLastRun($lastRun)
    {
        $this->lastRun = $lastRun;

        return $this;
    }

    /**
     * Get last run date/time
     *
     * @return \Datetime
     */
    public function getLastRun()
    {
        return $this->lastRun;
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->createdAt = $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Pre update event handler
     * @ORM\PreUpdate
     */
    public function doUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return MarketingListItem[]|Collection
     */
    public function getMarketingListItems()
    {
        return $this->marketingListItems;
    }

    /**
     * Set marketing list items.
     *
     * @param Collection|MarketingListItem[] $marketingListItems
     * @return MarketingList
     */
    public function resetMarketingListItems($marketingListItems)
    {
        $this->marketingListItems->clear();

        foreach ($marketingListItems as $marketingListItem) {
            $this->addMarketingListItem($marketingListItem);
        }

        return $this;
    }

    /**
     * Add marketing list item.
     *
     * @param MarketingListItem $marketingListItem
     * @return MarketingList
     */
    public function addMarketingListItem(MarketingListItem $marketingListItem)
    {
        if (!$this->marketingListItems->contains($marketingListItem)) {
            $this->marketingListItems->add($marketingListItem);
            $marketingListItem->setMarketingList($this);
        }

        return $this;
    }

    /**
     * Remove marketing list item.
     *
     * @param MarketingListItem $marketingListItem
     * @return MarketingList
     */
    public function removeMarketingListItem(MarketingListItem $marketingListItem)
    {
        if ($this->marketingListItems->contains($marketingListItem)) {
            $this->marketingListItems->removeElement($marketingListItem);
        }

        return $this;
    }

    /**
     * @return MarketingListItem[]|Collection
     */
    public function getMarketingListRemovedItems()
    {
        return $this->marketingListRemovedItems;
    }

    /**
     * Set marketing list removed items.
     *
     * @param Collection|MarketingListRemovedItem[] $marketingListRemovedItems
     * @return MarketingList
     */
    public function resetMarketingListRemovedItems($marketingListRemovedItems)
    {
        $this->marketingListRemovedItems->clear();

        foreach ($marketingListRemovedItems as $marketingListRemovedItem) {
            $this->addMarketingListRemovedItem($marketingListRemovedItem);
        }

        return $this;
    }

    /**
     * Add marketing list removed item.
     *
     * @param MarketingListRemovedItem $marketingListRemovedItem
     * @return MarketingList
     */
    public function addMarketingListRemovedItem(MarketingListRemovedItem $marketingListRemovedItem)
    {
        if (!$this->marketingListRemovedItems->contains($marketingListRemovedItem)) {
            $this->marketingListRemovedItems->add($marketingListRemovedItem);
            $marketingListRemovedItem->setMarketingList($this);
        }

        return $this;
    }

    /**
     * Remove marketing list item.
     *
     * @param MarketingListRemovedItem $marketingListRemovedItem
     * @return MarketingList
     */
    public function removeMarketingListRemovedItem(MarketingListRemovedItem $marketingListRemovedItem)
    {
        if ($this->marketingListRemovedItems->contains($marketingListRemovedItem)) {
            $this->marketingListRemovedItems->removeElement($marketingListRemovedItem);
        }

        return $this;
    }

    /**
     * @return MarketingListUnsubscribedItem[]|Collection
     */
    public function getMarketingListUnsubscribedItems()
    {
        return $this->marketingListUnsubscribedItems;
    }

    /**
     * Set marketing list unsubscribed items.
     *
     * This method could not be named setPhones because of bug CRM-253.
     *
     * @param Collection|MarketingListUnsubscribedItem[] $marketingListUnsubscribedItems
     * @return MarketingList
     */
    public function resetMarketingListUnsubscribedItems($marketingListUnsubscribedItems)
    {
        $this->marketingListUnsubscribedItems->clear();

        foreach ($marketingListUnsubscribedItems as $marketingListUnsubscribedItem) {
            $this->addMarketingListUnsubscribedItem($marketingListUnsubscribedItem);
        }

        return $this;
    }

    /**
     * Add marketing list unsubscribed item.
     *
     * @param MarketingListUnsubscribedItem $marketingListUnsubscribedItem
     * @return MarketingList
     */
    public function addMarketingListUnsubscribedItem(MarketingListUnsubscribedItem $marketingListUnsubscribedItem)
    {
        if (!$this->marketingListUnsubscribedItems->contains($marketingListUnsubscribedItem)) {
            $this->marketingListUnsubscribedItems->add($marketingListUnsubscribedItem);
            $marketingListUnsubscribedItem->setMarketingList($this);
        }

        return $this;
    }

    /**
     * Remove marketing list unsubscribed item.
     *
     * @param MarketingListUnsubscribedItem $marketingListUnsubscribedItem
     * @return MarketingList
     */
    public function removeMarketingListUnsubscribedItem(MarketingListUnsubscribedItem $marketingListUnsubscribedItem)
    {
        if ($this->marketingListUnsubscribedItems->contains($marketingListUnsubscribedItem)) {
            $this->marketingListUnsubscribedItems->removeElement($marketingListUnsubscribedItem);
        }

        return $this;
    }

    /**
     * Get this segment definition in YAML format
     *
     * @return string
     */
    public function getDefinition()
    {
        if ($this->segment) {
            return $this->segment->getDefinition();
        }

        return null;
    }

    /**
     * Set this segment definition in YAML format
     *
     * @param string $definition
     */
    public function setDefinition($definition)
    {
        if ($this->segment) {
            $this->segment->setDefinition($definition);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     * @return MarketingList
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
}
