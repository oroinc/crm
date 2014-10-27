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
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $subject;

    /**
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $description;

    /**
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $dueDate;

    /**
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $taskPriority;

    /**
     * @Soap\ComplexType("int", nillable=true)
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
        $this->taskPriority = $task->taskPriority ? $task->taskPriority->getName() : null;
        $task->owner = $this->getEntityId($task->owner);
        $this->createdAt = $task->createdAt;
        $this->updatedAt = $task->updatedAt;
        $task->workflowItem = $this->getEntityId($task->workflowItem);
        $task->workflowStep = $this->getEntityId($task->workflowStep);
    }

    /**
     * @param object $entity
     *
     * @return integer|null
     */
    protected function getEntityId($entity)
    {
        if ($entity) {
            return $entity->getId();
        }

        return null;
    }
}
