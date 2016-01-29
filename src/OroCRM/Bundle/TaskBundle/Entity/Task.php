<?php

namespace OroCRM\Bundle\TaskBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\ReminderBundle\Entity\RemindableInterface;
use Oro\Bundle\ReminderBundle\Model\ReminderData;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

use OroCRM\Bundle\TaskBundle\Model\ExtendTask;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_task",
 *      indexes={
 *          @ORM\Index(name="task_due_date_idx",columns={"due_date"}),
 *          @ORM\Index(name="task_updated_at_idx",columns={"updatedAt"}),
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\TaskBundle\Entity\Repository\TaskRepository")
 * @Config(
 *      routeName="orocrm_task_index",
 *      routeView="orocrm_task_view",
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-tasks"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL"
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "workflow"={
 *              "active_workflow"="task_flow"
 *          },
 *          "reminder"={
 *              "reminder_template_name"="task_reminder",
 *              "reminder_flash_template_identifier"="task_template"
 *          },
 *          "grouping"={
 *              "groups"={"activity"}
 *          },
 *          "activity"={
 *              "route"="orocrm_task_activity_view",
 *              "acl"="orocrm_task_view",
 *              "action_button_widget"="orocrm_add_task_button",
 *              "action_link_widget"="orocrm_add_task_link"
 *          },
 *          "tag"={
 *              "enabled"=true
 *          },
 *          "grid"={
 *              "default"="tasks-grid",
 *              "context"="task-for-context-grid"
 *          }
 *      }
 * )
 */
class Task extends ExtendTask implements RemindableInterface, DatesAwareInterface
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
     * @var \DateTime
     *
     * @ORM\Column(name="due_date", type="datetime", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $dueDate;

    /**
     * @var TaskPriority
     *
     * @ORM\ManyToOne(targetEntity="TaskPriority")
     * @ORM\JoinColumn(name="task_priority_name", referencedColumnName="name", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $taskPriority;

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
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

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
     * @ORM\Column(type="datetime")
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
     * @var bool
     */
    protected $updatedAtSet;

    public function __construct()
    {
        parent::__construct();

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
     * @return string
     */
    public function getWorkflowStepName()
    {
        return $this->getWorkflowStep() ? $this->getWorkflowStep()->getName() : null;
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
     * Set organization
     *
     * @param Organization $organization
     * @return Task
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

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

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
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAtSet = false;
        if ($updatedAt !== null) {
            $this->updatedAtSet = true;
        }

        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUpdatedAtSet()
    {
        return $this->updatedAtSet;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getSubject();
    }
}
