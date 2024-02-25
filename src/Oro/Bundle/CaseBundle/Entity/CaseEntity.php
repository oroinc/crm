<?php

namespace Oro\Bundle\CaseBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroCaseBundle_Entity_CaseEntity;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationInterface;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Holds case details.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @mixin OroCaseBundle_Entity_CaseEntity
 */
#[ORM\Entity]
#[ORM\Table(name: 'orocrm_case')]
#[ORM\Index(columns: ['reportedAt', 'id'], name: 'case_reported_at_idx')]
#[ORM\HasLifecycleCallbacks]
#[Config(
    routeName: 'oro_case_index',
    routeView: 'oro_case_view',
    defaultValues: [
    'dataaudit' => ['auditable' => true],
    'entity' => ['icon' => 'fa-list-alt'],
    'ownership' => [
        'owner_type' => 'USER',
        'owner_field_name' => 'owner',
        'owner_column_name' => 'owner_id',
        'organization_field_name' => 'organization',
        'organization_column_name' => 'organization_id'
    ],
    'security' => ['type' => 'ACL', 'category' => 'account_management'],
    'grid' => ['default' => 'cases-grid', 'context' => 'cases-for-context-grid'],
    'tag' => ['enabled' => true, 'immutable' => true]
    ]
)]
class CaseEntity implements EmailHolderInterface, ExtendEntityInterface
{
    use ExtendEntityTrait;

    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?int $id = null;

    #[ORM\Column(name: 'subject', type: Types::STRING, length: 255)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?string $subject = null;

    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?string $description = null;

    #[ORM\Column(name: 'resolution', type: Types::TEXT, nullable: true)]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?string $resolution = null;

    #[ORM\ManyToOne(targetEntity: CaseSource::class)]
    #[ORM\JoinColumn(name: 'source_name', referencedColumnName: 'name', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?CaseSource $source = null;

    #[ORM\ManyToOne(targetEntity: CaseStatus::class)]
    #[ORM\JoinColumn(name: 'status_name', referencedColumnName: 'name', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?CaseStatus $status = null;

    #[ORM\ManyToOne(targetEntity: CasePriority::class)]
    #[ORM\JoinColumn(name: 'priority_name', referencedColumnName: 'name', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?CasePriority $priority = null;

    #[ORM\ManyToOne(targetEntity: Contact::class)]
    #[ORM\JoinColumn(name: 'related_contact_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?Contact $relatedContact = null;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: 'related_account_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?Account $relatedAccount = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'assigned_to_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?User $assignedTo = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ConfigField(defaultValues: ['dataaudit' => ['auditable' => true]])]
    protected ?User $owner = null;

    /**
     * @var Collection<int, CaseComment>
     */
    #[ORM\OneToMany(mappedBy: 'case', targetEntity: CaseComment::class, cascade: ['ALL'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => Criteria::DESC])]
    protected ?Collection $comments = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[ConfigField(defaultValues: ['entity' => ['label' => 'oro.ui.created_at']])]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[ConfigField(defaultValues: ['entity' => ['label' => 'oro.ui.updated_at']])]
    protected ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected ?\DateTimeInterface $reportedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $closedAt = null;

    /**
     * Flag to update closedAt field when status is set to closed.
     *
     * @var bool
     */
    private $updateClosedAt = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?OrganizationInterface $organization = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $subject
     * @return CaseEntity
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $description
     * @return CaseEntity
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
     * @param string $resolution
     * @return CaseEntity
     */
    public function setResolution($resolution)
    {
        $this->resolution = $resolution;

        return $this;
    }

    /**
     * @return string
     */
    public function getResolution()
    {
        return $this->resolution;
    }

    /**
     * @param CaseSource|null $source
     * @return CaseEntity
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return CaseSource
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param CaseStatus|null $status
     * @return CaseEntity
     */
    public function setStatus($status)
    {
        $this->updateClosedAt($status, $this->status);
        $this->status = $status;

        return $this;
    }

    /**
     * @param mixed $newStatus
     * @param mixed $oldStatus
     */
    protected function updateClosedAt($newStatus, $oldStatus)
    {
        if ($newStatus instanceof CaseStatus &&
            $newStatus->getName() == CaseStatus::STATUS_CLOSED &&
            !$newStatus->isEqualTo($oldStatus)
        ) {
            $this->updateClosedAt = true;
        } else {
            $this->updateClosedAt = null;
        }
    }

    /**
     * @return CaseStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param CasePriority|null $priority
     * @return CaseEntity
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return CasePriority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param Contact|null $relatedContact
     * @return CaseEntity
     */
    public function setRelatedContact($relatedContact = null)
    {
        $this->relatedContact = $relatedContact;

        return $this;
    }

    /**
     * @return Contact
     */
    public function getRelatedContact()
    {
        return $this->relatedContact;
    }

    /**
     * @param Account|null $relatedAccount
     * @return CaseEntity
     */
    public function setRelatedAccount($relatedAccount = null)
    {
        $this->relatedAccount = $relatedAccount;

        return $this;
    }

    /**
     * @return Account
     */
    public function getRelatedAccount()
    {
        return $this->relatedAccount;
    }

    /**
     * @param User $assignee
     * @return CaseEntity
     */
    public function setAssignedTo($assignee)
    {
        $this->assignedTo = $assignee;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    /**
     * @param User $owner
     * @return CaseEntity
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param CaseComment $comment
     * @return CaseEntity
     */
    public function addComment(CaseComment $comment)
    {
        $this->comments->add($comment);
        $comment->setCase($this);

        return $this;
    }

    /**
     * @param CaseComment $comment
     * @return CaseEntity
     */
    public function removeComment(CaseComment $comment)
    {
        $this->comments->removeElement($comment);

        return $this;
    }

    /**
     * @param \DateTime|null $createdAt
     * @return CaseEntity
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

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
     * @param \DateTime|null $updatedAt
     * @return CaseEntity
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|null $reportedAt
     * @return CaseEntity
     */
    public function setReportedAt(\DateTime $reportedAt = null)
    {
        $this->reportedAt = $reportedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getReportedAt()
    {
        return $this->reportedAt;
    }

    /**
     * @param \DateTime|null $closedAt
     * @return CaseEntity
     */
    public function setClosedAt(\DateTime $closedAt = null)
    {
        $this->closedAt = $closedAt;

        if ($this->closedAt) {
            $this->updateClosedAt = null;
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getClosedAt()
    {
        return $this->closedAt;
    }

    /**
     * Get the primary email address of the related contact
     *
     * @return string
     */
    public function getEmail()
    {
        $contact = $this->getRelatedContact();
        if (!$contact) {
            return null;
        }

        return $contact->getEmail();
    }

    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->createdAt  = $this->createdAt ? $this->createdAt : new \DateTime('now', new \DateTimeZone('UTC'));
        $this->reportedAt = $this->reportedAt ? $this->reportedAt : new \DateTime('now', new \DateTimeZone('UTC'));
        if ($this->updateClosedAt && !$this->closedAt) {
            $this->setClosedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        }
    }

    #[ORM\PreUpdate]
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        if ($this->updateClosedAt) {
            $this->setClosedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->subject;
    }

    /**
     * Set organization
     *
     * @param Organization|null $organization
     * @return CaseEntity
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
