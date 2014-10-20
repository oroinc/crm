<?php

namespace OroCRM\Bundle\TaskBundle\Entity\Manager;

use OroCRM\Bundle\TaskBundle\Entity\Task;

class TaskActivityManager
{
    /**
     * @param Task  $task
     * @param object $target
     * @return bool TRUE if the association was added; otherwise, FALSE
     */
    public function addAssociation(Task $task, $target)
    {
        $task->addActivityTarget($target);

        return true;
    }
}
