<?php

namespace OroCRM\Bundle\ChannelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

/**
 * @ORM\Entity()
 * @ORM\Table(name="orocrm_channel")
 * @Config(
 *  routeView="orocrm_channel_view",
 *  defaultValues={
 *      "entity"={"icon"="icon-sitemap"},
 *      "ownership"={
 *          "owner_type"="ORGANIZATION",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="organization_owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class Channel
{
    const STATUS_ACTIVE   = 1;
    const STATUS_INACTIVE = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="OroCRM\Bundle\ChannelBundle\Entity\EntityName",
     *     cascade={"all"}, mappedBy="channel", orphanRemoval=true
     * )
     */
    protected $entities;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var Integration
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\IntegrationBundle\Entity\Channel", cascade={"all"})
     * @ORM\JoinColumn(name="data_source_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $dataSource;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_identity", type="string", length=255, nullable=false)
     */
    protected $customerIdentity;

    /**
     * @var string
     *
     * @ORM\Column(name="channel_type", type="string", nullable=false)
     */
    protected $channelType;

    public function __construct()
    {
        $this->status       = self::STATUS_INACTIVE;
        $this->entities     = new ArrayCollection();
    }

    /**
     * @return int
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
     * @param array $entities
     */
    public function setEntities(array $entities)
    {
        list($stillPresent, $removed) = $this->getEntitiesCollection()->partition(
            function ($key, EntityName $entityName) use ($entities) {
                return in_array($entityName->getValue(), $entities, true);
            }
        );

        $stillPresent = array_map(
            function (EntityName $entityName) {
                return $entityName->getValue();
            },
            $stillPresent->toArray()
        );

        $added = array_diff($entities, $stillPresent);
        foreach ($added as $entity) {
            $entityName = new EntityName($entity);
            $this->getEntitiesCollection()->add($entityName);
            $entityName->setChannel($this);
        }

        foreach ($removed as $entityName) {
            $this->getEntitiesCollection()->removeElement($entityName);
        }
    }

    /**
     * @return array
     */
    public function getEntities()
    {
        $entitiesCollection = $this->getEntitiesCollection();

        $values = array_map(
            function (EntityName $entityName) {
                return $entityName->getValue();
            },
            $entitiesCollection->toArray()
        );

        return $values;
    }

    /**
     * @return ArrayCollection
     */
    public function getEntitiesCollection()
    {
        return $this->entities;
    }

    /**
     * @param Organization $owner
     */
    public function setOwner(Organization $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return Organization
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Integration $dataSource
     */
    public function setDataSource(Integration $dataSource = null)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * @return Integration
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * @param boolean $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $customerIdentity
     */
    public function setCustomerIdentity($customerIdentity)
    {
        $this->customerIdentity = $customerIdentity;
    }

    /**
     * @return EntityName
     */
    public function getCustomerIdentity()
    {
        return $this->customerIdentity;
    }

    /**
     * @param string $channelType
     */
    public function setChannelType($channelType)
    {
        $this->channelType = $channelType;
    }

    /**
     * @return string
     */
    public function getChannelType()
    {
        return $this->channelType;
    }
}
