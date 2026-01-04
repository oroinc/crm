<?php

namespace Oro\Bundle\ChannelBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\ChannelBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

/**
 * Represents the source of customer data.
 */
#[ORM\Entity(repositoryClass: ChannelRepository::class)]
#[ORM\Table(name: 'orocrm_channel')]
#[ORM\Index(columns: ['name'], name: 'crm_channel_name_idx')]
#[ORM\Index(columns: ['status'], name: 'crm_channel_status_idx')]
#[ORM\Index(columns: ['channel_type'], name: 'crm_channel_channel_type_idx')]
#[ORM\HasLifecycleCallbacks]
#[Config(
    routeName: 'oro_channel_index',
    routeView: 'oro_channel_view',
    defaultValues: [
        'entity' => ['icon' => 'fa-sitemap'],
        'ownership' => [
            'owner_type' => 'ORGANIZATION',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'organization_owner_id'
        ],
        'security' => ['type' => 'ACL', 'group_name' => '', 'category' => 'account_management'],
        'form' => ['form_type' => ChannelSelectType::class],
        'grid' => ['default' => 'oro-channels-grid']
    ]
)]
class Channel
{
    public const STATUS_ACTIVE = true;
    public const STATUS_INACTIVE = false;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ConfigField(defaultValues: ['importexport' => ['order' => 0]])]
    protected ?int $id = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255, nullable: false)]
    #[ConfigField(
        defaultValues: ['dataaudit' => ['auditable' => true], 'importexport' => ['identity' => true, 'order' => 10]]
    )]
    protected ?string $name = null;

    /**
     * @var Collection<int, EntityName>
     */
    #[ORM\OneToMany(mappedBy: 'channel', targetEntity: EntityName::class, cascade: ['all'], orphanRemoval: true)]
    protected ?Collection $entities = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Organization $owner = null;

    #[ORM\OneToOne(targetEntity: Integration::class, cascade: ['all'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'data_source_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Integration $dataSource = null;

    #[ORM\Column(name: 'status', type: Types::BOOLEAN, nullable: false)]
    #[ConfigField(defaultValues: ['importexport' => ['full' => true, 'order' => 20]])]
    protected ?bool $status = self::STATUS_INACTIVE;

    #[ORM\Column(name: 'customer_identity', type: Types::STRING, length: 255, nullable: false)]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?string $customerIdentity = null;

    #[ORM\Column(name: 'channel_type', type: Types::STRING, nullable: false)]
    #[ConfigField(defaultValues: ['importexport' => ['excluded' => true]])]
    protected ?string $channelType = null;

    /**
     * @var array $data
     */
    #[ORM\Column(name: 'data', type: 'json_array', nullable: true)]
    protected $data;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[ConfigField(
        defaultValues: ['entity' => ['label' => 'oro.ui.created_at'], 'importexport' => ['excluded' => true]]
    )]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[ConfigField(
        defaultValues: ['entity' => ['label' => 'oro.ui.updated_at'], 'importexport' => ['excluded' => true]]
    )]
    protected ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
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
        [$stillPresent, $removed] = $this->getEntitiesCollection()->partition(
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
     * @param Integration|null $dataSource
     *
     * @return Channel
     */
    public function setDataSource(?Integration $dataSource = null)
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
     * @param \DateTime|null $createdAt
     *
     * @return Channel
     */
    public function setCreatedAt(?\DateTime $createdAt = null)
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

    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        if (null === $this->createdAt) {
            $this->createdAt = clone $this->updatedAt;
        }
    }

    #[ORM\PreUpdate]
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return string
     */
    #[\Override]
    public function __toString()
    {
        return (string)$this->getName();
    }
}
