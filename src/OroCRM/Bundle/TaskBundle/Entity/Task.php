<?php

namespace OroCRM\Bundle\TaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

use JMS\Serializer\Annotation as JMS;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

/**
 * @ORM\Entity
 * @ORM\Table(name="orocrm_task")
 * @Oro\Loggable
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\TaskBundle\Entity\Repository\TaskRepository")
 * @Config(
 *  defaultValues={
 *      "ownership"={
 *          "owner_type"="USER",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="owner_id"
 *      },
 *      "security"={
 *          "type"="ACL"
 *      },
 *      "dataaudit"={"auditable"=true}
 *  }
 * )
 */
class Task
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Type("integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255, nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "email"={"available_in_template"=true}
     *  }
     * )
     * @JMS\Type("string")
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "email"={"available_in_template"=true}
     *  }
     * )
     * @JMS\Type("string")
     */
    protected $description;

    /**
     * @var \Datetime
     *
     * @Oro\Versioned
     * @ORM\Column(name="due_date", type="datetime")
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "email"={"available_in_template"=true}
     *  }
     * )
     * @JMS\Type("dateTime")
     */
    protected $dueDate;

    /**
     * @var \DateTime $created
     *
     * @ORM\Column(type="datetime")
     * @JMS\Type("DateTime")
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime $updated
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Type("DateTime")
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $updatedAt;

    /**
     * @var TaskPriority
     *
     * @ORM\ManyToOne(targetEntity="TaskPriority")
     * @ORM\JoinColumn(name="priority_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $priority;

    /**
     * @var WorkflowStep
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowStep")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $status;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="assigned_to_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned("getUsername")
     * @JMS\Type("integer")
     * @JMS\Accessor(getter="getAssigneeToId")
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $assignedTo;

    /**
     * @var Collection
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\AccountBundle\Entity\Account")
     * @ORM\JoinColumn(name="relatedAccount", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     * @JMS\Type("integer")
     * @JMS\Accessor(getter="getRelatedAccountId")
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $relatedAccount;

    /**
     * @var Collection
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact")
     * @ORM\JoinColumn(name="relatedContact", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     * @JMS\Type("integer")
     * @JMS\Accessor(getter="getRelatedContactId")
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $relatedContact;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned("getUsername")
     * @JMS\Type("integer")
     * @JMS\Accessor(getter="getOwnerId")
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $owner;


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $description
     */
    public function setSubject($description)
    {
        $this->subject = $description;
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
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return \Datetime
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param \DateTime $dueDate
     */
    public function setDueDate(\DateTime $dueDate)
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return Priority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param Priority $priority
     */
    public function setPriority(Priority $priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return WorkflowStep
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param WorkflowStep $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return User
     */
    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    public function getAssigneeToId()
    {
        return $this->getAssignedTo() ? $this->getAssignedTo()->getId() : null;
    }

    /**
     * @param \Oro\Bundle\UserBundle\Entity\User $assignedTo
     */
    public function setAssignedTo(User $assignedTo = null)
    {
        $this->assignedTo = $assignedTo;
    }

    /**
     * @return Account
     */
    public function getRelatedAccount()
    {
        return $this->relatedAccount;
    }

    public function getRelatedAccountId()
    {
        return $this->getRelatedAccount() ? $this->getRelatedAccount()->getId() : null;
    }

    /**
     * @param Account $account
     */
    public function setRelatedAccount(Account $account = null)
    {
        $this->relatedAccount = $account;
    }

    /**
     * @return Contact
     */
    public function getRelatedContact()
    {
        return $this->relatedContact;
    }

    /**
     * @return Contact
     */
    public function getRelatedContactId()
    {
        return $this->getRelatedContact() ? $this->getRelatedContact()->getId() : null;
    }

    /**
     * @param Contact $contact
     */
    public function setRelatedContact(Contact $contact = null)
    {
        $this->relatedContact = $contact;
    }

    /**
     * @return string
     */
    public function getStatusName()
    {
        return $this->getStatus() ? $this->getStatus()->getName() : null;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        return $this->getOwner() ? $this->getOwner()->getId() : null;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}
