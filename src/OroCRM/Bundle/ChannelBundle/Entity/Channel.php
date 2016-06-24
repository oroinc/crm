<?php

namespace OroCRM\Bundle\ChannelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\ChannelBundle\Entity\Repository\ChannelRepository")
 * @ORM\Table(name="orocrm_channel", indexes={
 *     @ORM\Index(name="crm_channel_name_idx", columns={"name"}),
 *     @ORM\Index(name="crm_channel_status_idx", columns={"status"}),
 *     @ORM\Index(name="crm_channel_channel_type_idx", columns={"channel_type"})
 * })
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *  routeName="orocrm_channel_index",
 *  routeView="orocrm_channel_view",
 *  defaultValues={
 *      "entity"={
 *          "icon"="icon-sitemap"
 *      },
 *      "ownership"={
 *          "owner_type"="ORGANIZATION",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="organization_owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"="",
 *          "category"="account_management"
 *      },
 *      "form"={
 *            "form_type"="orocrm_channel_select_type"
 *      },
 *      "grid"={
 *          "default"="orocrm-channels-grid"
 *     }
 *  }
 * )
 */
class Channel
{
    const STATUS_ACTIVE = true;
    const STATUS_INACTIVE = false;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *  defaultValues={
     *      "importexport"={
     *          "order"=0
     *      }
     *  }
     * )
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *          "importexport"={
     *              "identity"=true,
     *              "order"=10
     *          }
     *      }
     * )
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
     * @ORM\OneToOne(targetEntity="Oro\Bundle\IntegrationBundle\Entity\Channel", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="data_source_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $dataSource;

    /**
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "full"=true,
     *              "order"=20
     *          }
     *      }
     * )
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_identity", type="string", length=255, nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $customerIdentity;

    /**
     * @var string
     *
     * @ORM\Column(name="channel_type", type="string", nullable=false)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $channelType;

    /**
     * @var array $data
     *
     * @ORM\Column(name="data", type="json_array", nullable=true)
     */
    protected $data;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->status   = self::STATUS_INACTIVE;
        $this->entities = new ArrayCollection();
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
     *
     * @return Channel
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     *
     * @return Channel
     */
    public function setEntities(array $entities)
    {
        list($stillPresent, $removed) = $this->getEntitiesCollection()->partition(
            function ($key, EntityName $entityName) use ($entities) {
                return in_array($entityName->getName(), $entities, true);
            }
        );

        $stillPresent = array_map(
            function (EntityName $entityName) {
                return $entityName->getName();
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

        return $this;
    }

    /**
     * @return array
     */
    public function getEntities()
    {
        $entitiesCollection = $this->getEntitiesCollection();

        $values = array_map(
            function (EntityName $entityName) {
                return $entityName->getName();
            },
            $entitiesCollection->toArray()
        );

        return array_values($values);
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
     *
     * @return Channel
     */
    public function setOwner(Organization $owner)
    {
        $this->owner = $owner;

        return $this;
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
     *
     * @return Channel
     */
    public function setDataSource(Integration $dataSource = null)
    {
        $this->dataSource = $dataSource;

        return $this;
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
     *
     * @return Channel
     */
    public function setStatus($status)
    {
        $this->status = (bool)$status;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getStatus()
    {
        return (bool)$this->status;
    }

    /**
     * @param string $customerIdentity
     *
     * @return Channel
     */
    public function setCustomerIdentity($customerIdentity)
    {
        $this->customerIdentity = $customerIdentity;

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerIdentity()
    {
        return $this->customerIdentity;
    }

    /**
     * @param string $channelType
     *
     * @return Channel
     */
    public function setChannelType($channelType)
    {
        $this->channelType = $channelType;

        return $this;
    }

    /**
     * @return string
     */
    public function getChannelType()
    {
        return $this->channelType;
    }

    /**
     * @param array $data
     * @return Channel
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return Channel
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return Channel
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        if (!$this->getCreatedAt()) {
            $this->setCreatedAt($now);
        }

        $this->setUpdatedAt($now);
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getName();
    }
}
