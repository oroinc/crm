<?php

namespace OroCRM\Bundle\TaskBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SoapBundle\Entity\SoapEntityInterface;

/**
 * @Soap\Alias("OroCRM.Bundle.TaskBundle.Entity.Task")
 */
class TaskSoap extends Task implements SoapEntityInterface
{
    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $id;

    /**
     * @Soap\ComplexType("string", nillable=false)
     */
    protected $subject;

    /**
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $description;

    /**
     * @Soap\ComplexType("dateTime", nillable=false)
     */
    protected $dueDate;

    /**
     * @Soap\ComplexType("string", nillable=false)
     */
    protected $taskPriority;

    /**
     * @Soap\ComplexType("int", nillable=false)
     */
    protected $assignedTo;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $relatedAccount;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $relatedContact;

    /**
     * @Soap\ComplexType("int", nillable=false)
     */
    protected $owner;

    /**
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $createdAt;

    /**
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $updatedAt;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $workflowItem;

    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $workflowStep;

    /**
     * @param Task $task
     */
    public function soapInit($task)
    {
        $this->id = $task->id;
        $this->subject = $task->subject;
        $this->description = $task->description;
        $this->dueDate = $task->dueDate;
        $this->dueDate = $task->dueDate;
        $this->taskPriority = $task->taskPriority ? $task->taskPriority->getName() : null;
        $this->assignedTo = $task->taskPriority ? $task->assignedTo->getId() : null;
        $this->relatedAccount = $task->relatedAccount ? $task->relatedAccount->getId() : null;
        $this->relatedContact = $task->relatedContact ? $task->relatedContact->getId() : null;
        $this->owner = $task->owner ? $task->owner->getId() : null;
        $this->createdAt = $task->createdAt;
        $this->updatedAt = $task->updatedAt;
        $this->workflowItem = $task->workflowItem ? $task->workflowItem->getId() : null;
        $this->workflowStep = $task->workflowStep ? $task->workflowStep->getId() : null;
    }
}
