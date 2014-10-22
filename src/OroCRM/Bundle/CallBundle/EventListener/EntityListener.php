<?php

namespace OroCRM\Bundle\CallBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;

use OroCRM\Bundle\CallBundle\Entity\Manager\CallActivityManager;

class EntityListener
{
    /** @var CallActivityManager */
    protected $callActivityManager;

    /**
     * @param CallActivityManager $callActivityManager
     */
    public function __construct(CallActivityManager $callActivityManager)
    {
        $this->callActivityManager = $callActivityManager;
    }

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        $this->callActivityManager->handleOnFlush($event);
    }
}
