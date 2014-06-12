<?php

namespace OroCRM\Bundle\CaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\CaseBundle\Model\ExtendCaseEntity;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\AccountBundle\Entity\Account;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_case"
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 * @Config(
 *  routeName="orocrm_case_index",
 *  routeView="orocrm_case_view",
 *  defaultValues={
 *      "dataaudit"={"auditable"=true},
 *      "entity"={
 *          "icon"="icon-list-alt"
 *      },
 *      "ownership"={
 *          "owner_type"="USER",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="owner_id"
 *      },
 *      "security"={
 *          "type"="ACL"
 *      }
 *  }
 * )
 */
class CaseEntity extends ExtendCaseEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     * @Oro\Versioned
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Oro\Versioned
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="resolution", type="text", nullable=true)
     * @Oro\Versioned
     */
    protected $resolution;

    /**
     * @var CaseOrigin
     *
     * @ORM\ManyToOne(targetEntity="CaseOrigin", cascade={"persist"})
     * @ORM\JoinColumn(name="origin_name", referencedColumnName="name", onDelete="SET NULL")
     * @Oro\Versioned
     */
    protected $origin;

    /**
     * @var CaseStatus
     *
     * @ORM\ManyToOne(targetEntity="CaseStatus", cascade={"persist"})
     * @ORM\JoinColumn(name="status_name", referencedColumnName="name", onDelete="SET NULL")
     * @Oro\Versioned
     */
    protected $status;

    /**
     * @var CasePriority
     *
     * @ORM\ManyToOne(targetEntity="CasePriority", cascade={"persist"})
     * @ORM\JoinColumn(name="priority_name", referencedColumnName="name", onDelete="SET NULL")
     * @Oro\Versioned
     */
    protected $priority;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact")
     * @ORM\JoinColumn(name="related_contact_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $relatedContact;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\AccountBundle\Entity\Account")
     * @ORM\JoinColumn(name="related_account_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $relatedAccount;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="assigned_to_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     */
    protected $assignedTo;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     */
    protected $owner;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
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
     * @param integer $id
     * @return CaseEntity
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * @param CaseOrigin|null $origin
     * @return CaseEntity
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * @return CaseOrigin
     */
    public function getOrigin()
    {
        return $this->origin;
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
            $this->closedAt = new \DateTime();
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
    public function setRelatedContact(Contact $relatedContact = null)
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
    public function setRelatedAccount(Account $relatedAccount = null)
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
     * @param \DateTime $createdAt
     * @return CaseEntity
     */
    public function setCreatedAt(\DateTime $createdAt)
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
     * @param \DateTime $updatedAt
     * @return CaseEntity
     */
    public function setUpdatedAt(\DateTime $updatedAt)
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
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt  = $this->createdAt ? $this->createdAt : new \DateTime();
        $this->reportedAt = $this->reportedAt? $this->reportedAt : new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}
