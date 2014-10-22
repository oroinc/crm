<?php

namespace OroCRM\Bundle\TaskBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;

use OroCRM\Bundle\TaskBundle\Entity\Manager\TaskActivityManager;

class EntityListener
{
    /** @var TaskActivityManager */
    protected $taskActivityManager;

    /**
     * @param TaskActivityManager $taskActivityManager
     */
    public function __construct(TaskActivityManager $taskActivityManager)
    {
        $this->taskActivityManager = $taskActivityManager;
    }

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        $this->taskActivityManager->handleOnFlush($event);
    }
}
