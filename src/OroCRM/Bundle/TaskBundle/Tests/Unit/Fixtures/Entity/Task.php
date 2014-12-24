<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Unit\Fixtures\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

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
 */
class Task extends ExtendTask implements RemindableInterface
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
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="due_date", type="datetime", nullable=true)
     */
    protected $dueDate;

    /**
     * @ORM\ManyToOne(targetEntity="TaskPriority")
     * @ORM\JoinColumn(name="task_priority_name", referencedColumnName="name", onDelete="SET NULL")
     */
    protected $taskPriority;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @ORM\OneToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowItem")
     * @ORM\JoinColumn(name="workflow_item_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowItem;

    /**
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
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

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
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
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
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getSubject();
    }
}
