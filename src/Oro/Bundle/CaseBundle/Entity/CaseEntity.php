<?php

namespace Oro\Bundle\CaseBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Holds case details.
 *
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_case",
 *      indexes={@ORM\Index(name="case_reported_at_idx",columns={"reportedAt", "id"})}
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="oro_case_index",
 *      routeView="oro_case_view",
 *      defaultValues={
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "entity"={
 *              "icon"="fa-list-alt"
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
 *              "category"="account_management"
 *          },
 *          "grid"={
 *              "default"="cases-grid",
 *              "context"="cases-for-context-grid"
 *          },
 *          "tag"={
 *              "enabled"=true,
 *              "immutable"=true
 *          }
 *      }
 * )
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CaseEntity implements EmailHolderInterface, ExtendEntityInterface
{
    use ExtendEntityTrait;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="resolution", type="text", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $resolution;

    /**
     * @var CaseSource
     *
     * @ORM\ManyToOne(targetEntity="CaseSource")
     * @ORM\JoinColumn(name="source_name", referencedColumnName="name", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $source;

    /**
     * @var CaseStatus
     *
     * @ORM\ManyToOne(targetEntity="CaseStatus")
     * @ORM\JoinColumn(name="status_name", referencedColumnName="name", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $status;

    /**
     * @var CasePriority
     *
     * @ORM\ManyToOne(targetEntity="CasePriority")
     * @ORM\JoinColumn(name="priority_name", referencedColumnName="name", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $priority;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\ContactBundle\Entity\Contact")
     * @ORM\JoinColumn(name="related_contact_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $relatedContact;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AccountBundle\Entity\Account")
     * @ORM\JoinColumn(name="related_account_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $relatedAccount;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="assigned_to_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $assignedTo;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $owner;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="CaseComment",
     *     mappedBy="case",
     *     cascade={"ALL"},
     *     orphanRemoval=true
     * )
     * @ORM\OrderBy({"createdAt"="DESC"})
     */
    protected $comments;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
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
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          }
     *      }
     * )
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $reportedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $closedAt;

    /**
     * Flag to update closedAt field when status is set to closed.
     *
     * @var bool
     */
    private $updateClosedAt = null;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

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
     * @param \DateTime $reportedAt
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
     * @param \DateTime $closedAt
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

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt  = $this->createdAt ? $this->createdAt : new \DateTime('now', new \DateTimeZone('UTC'));
        $this->reportedAt = $this->reportedAt? $this->reportedAt : new \DateTime('now', new \DateTimeZone('UTC'));
        if ($this->updateClosedAt && !$this->closedAt) {
            $this->setClosedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        }
    }

    /**
     * @ORM\PreUpdate
     */
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
     * @param Organization $organization
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
