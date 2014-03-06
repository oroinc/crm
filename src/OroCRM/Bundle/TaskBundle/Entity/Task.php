<?php

namespace OroCRM\Bundle\TaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation as JMS;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\TaskBundle\Model\ExtendTask;

/**
 * @ORM\Entity
 * @ORM\Table(name="orocrm_task")
 * @Oro\Loggable
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\TaskBundle\Entity\Repository\TaskRepository")
 * @Config(
 *  routeName="orocrm_task_index",
 *  routeView="orocrm_task_view",
 *  defaultValues={
 *      "entity"={
 *          "icon"="icon-tasks"
 *      },
 *      "ownership"={
 *          "owner_type"="USER",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="owner_id"
 *      },
 *      "security"={
 *          "type"="ACL"
 *      },
 *      "dataaudit"={"auditable"=true},
 *      "workflow"={
 *          "active_workflow"="task_flow"
 *      }
 *  }
 * )
 */
class Task extends ExtendTask
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
     * @ORM\Column(name="subject", type="string", length=255, nullable=true)
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "email"={"available_in_template"=true}
     *  }
     * )
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
     */
    protected $description;

    /**
     * @var \DateTime
     *
     * @Oro\Versioned
     * @ORM\Column(name="due_date", type="datetime")
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $dueDate;

    /**
     * @var TaskPriority
     *
     * @ORM\ManyToOne(targetEntity="TaskPriority")
     * @ORM\JoinColumn(name="task_priority_name", referencedColumnName="name", onDelete="SET NULL")
     */
    protected $taskPriority;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="assigned_to_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned("getUsername")
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $assignedTo;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\AccountBundle\Entity\Account")
     * @ORM\JoinColumn(name="related_account_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $relatedAccount;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact")
     * @ORM\JoinColumn(name="related_contact_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
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
     * @ConfigField(
     *  defaultValues={
     *      "dataaudit"={"auditable"=true},
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $owner;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $updatedAt;

    /**
     * TODO: Move field to custom entity config https://magecore.atlassian.net/browse/BAP-2923
     *
     * @var WorkflowItem
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowItem")
     * @ORM\JoinColumn(name="workflow_item_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowItem;

    /**
     * TODO: Move field to custom entity config https://magecore.atlassian.net/browse/BAP-2923
     *
     * @var WorkflowStep
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowStep")
     * @ORM\JoinColumn(name="workflow_step_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowStep;

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
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
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
     * @return \DateTime
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param \DateTime $dueDate
     */
    public function setDueDate(\DateTime $dueDate = null)
    {
        $this->dueDate = $dueDate;
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
     * @return TaskPriority
     */
    public function getTaskPriority()
    {
        return $this->taskPriority;
    }

    /**
     * @param TaskPriority $taskPriority
     */
    public function setTaskPriority(TaskPriority $taskPriority)
    {
        $this->taskPriority = $taskPriority;
    }

    /**
     * @return WorkflowStep
     */
    public function getStatus()
    {
        return $this->workflowStep;
    }

    /**
     * @return User
     */
    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    /**
     * @return mixed|null
     */
    public function getAssigneeToId()
    {
        return $this->getAssignedTo() ? $this->getAssignedTo()->getId() : null;
    }

    /**
     * @param User $assignedTo
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

    /**
     * @return mixed|null
     */
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
     * @param WorkflowItem $workflowItem
     */
    public function setWorkflowItem($workflowItem)
    {
        $this->workflowItem = $workflowItem;
    }

    /**
     * @return WorkflowItem
     */
    public function getWorkflowItem()
    {
        return $this->workflowItem;
    }

    /**
     * @param WorkflowItem $workflowStep
     */
    public function setWorkflowStep($workflowStep)
    {
        $this->workflowStep = $workflowStep;
    }

    /**
     * @return WorkflowStep
     */
    public function getWorkflowStep()
    {
        return $this->workflowStep;
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
