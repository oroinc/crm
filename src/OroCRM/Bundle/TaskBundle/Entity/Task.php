<?php

namespace OroCRM\Bundle\TaskBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\ReminderBundle\Entity\RemindableInterface;
use Oro\Bundle\ReminderBundle\Model\ReminderData;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\TaskBundle\Model\ExtendTask;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_task",
 *      indexes={@ORM\Index(name="task_due_date_idx",columns={"due_date"})}
 * )
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
 *      "workflow"={
 *          "active_workflow"="task_flow"
 *      },
 *      "reminder"={
 *          "reminder_template_name"="task_reminder",
 *          "reminder_flash_template_identifier"="task_template"
 *      },
 *  }
 * )
 */
class Task extends ExtendTask implements RemindableInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255, nullable=true)
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="due_date", type="datetime")
     * @ConfigField(
     *  defaultValues={
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
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $taskPriority;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $owner;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\AccountBundle\Entity\Account")
     * @ORM\JoinColumn(name="related_account_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *  defaultValues={
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
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $relatedContact;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="reporter_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *  defaultValues={
     *      "email"={"available_in_template"=true}
     *  }
     * )
     */
    protected $reporter;

    /**
     * @var WorkflowItem
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowItem")
     * @ORM\JoinColumn(name="workflow_item_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowItem;

    /**
     * @var WorkflowStep
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowStep")
     * @ORM\JoinColumn(name="workflow_step_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowStep;

    /**
     * @var Collection
     */
    protected $reminders;

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

    public function __construct()
    {
        $this->reminders = new ArrayCollection();
    }

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
     * @return bool
     */
    public function isDueDateExpired()
    {
        return $this->getDueDate() &&  $this->getDueDate() < new \DateTime();
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
    public function setTaskPriority($taskPriority)
    {
        $this->taskPriority = $taskPriority;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return mixed|null
     */
    public function getOwnerId()
    {
        return $this->getOwner() ? $this->getOwner()->getId() : null;
    }

    /**
     * @param User $owner
     */
    public function setOwner($owner = null)
    {
        $this->owner = $owner;
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
    public function setRelatedAccount($account = null)
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
    public function setRelatedContact($contact = null)
    {
        $this->relatedContact = $contact;
    }

    /**
     * @return string
     */
    public function getWorkflowStepName()
    {
        return $this->getWorkflowStep() ? $this->getWorkflowStep()->getName() : null;
    }

    /**
     * @return User
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * @return int
     */
    public function getReporterId()
    {
        return $this->getReporter() ? $this->getReporter()->getId() : null;
    }

    /**
     * @param User $reporter
     */
    public function setReporter($reporter = null)
    {
        $this->reporter = $reporter;
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
     * {@inheritdoc}
     */
    public function getReminders()
    {
        return $this->reminders;
    }

    /**
     * {@inheritdoc}
     */
    public function setReminders(Collection $reminders)
    {
        $this->reminders = $reminders;
    }

    /**
     * {@inheritdoc}
     */
    public function getReminderData()
    {
        $result = new ReminderData();

        $result->setSubject($this->getSubject());
        $result->setExpireAt($this->getDueDate());
        $result->setRecipient($this->getOwner());

        return $result;
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
