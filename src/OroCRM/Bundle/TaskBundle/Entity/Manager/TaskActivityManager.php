<?php

namespace OroCRM\Bundle\TaskBundle\Entity\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\ActivityBundle\Manager\ActivityManager;

use OroCRM\Bundle\TaskBundle\Entity\Task;

class TaskActivityManager
{
    /** @var ActivityManager */
    protected $activityManager;

    /**
     * @param ActivityManager $activityManager
     */
    public function __construct(ActivityManager $activityManager)
    {
        $this->activityManager = $activityManager;
    }

    /**
     * @param Task   $task
     * @param object $target
     *
     * @return bool TRUE if the association was added; otherwise, FALSE
     */
    public function addAssociation(Task $task, $target)
    {
        return $this->activityManager->addActivityTarget($task, $target);
    }

    /**
     * Handle onFlush event
     *
     * @param OnFlushEventArgs $event
     */
    public function handleOnFlush(OnFlushEventArgs $event)
    {
        $em  = $event->getEntityManager();
        $uow = $em->getUnitOfWork();

        $newEntities = $uow->getScheduledEntityInsertions();
        foreach ($newEntities as $entity) {
            if ($entity instanceof Task) {
                $hasChanges = $this->activityManager->addActivityTarget($entity, $entity->getOwner());
                // recompute change set if needed
                if ($hasChanges) {
                    $uow->computeChangeSet(
                        $em->getClassMetadata(ClassUtils::getClass($entity)),
                        $entity
                    );
                }
            }
        }

        $changedEntities = $uow->getScheduledEntityUpdates();
        foreach ($changedEntities as $entity) {
            if ($entity instanceof Task) {
                $hasChanges = false;
                $changeSet  = $uow->getEntityChangeSet($entity);
                foreach ($changeSet as $field => $values) {
                    if ($field === 'owner') {
                        list($oldValue, $newValue) = $values;
                        if ($oldValue !== $newValue) {
                            if ($this->activityManager->removeActivityTarget($entity, $oldValue)) {
                                $hasChanges = true;
                            }
                            if ($this->activityManager->addActivityTarget($entity, $newValue)) {
                                $hasChanges = true;
                            }
                        }
                        break;
                    }
                }
                // recompute change set if needed
                if ($hasChanges) {
                    $uow->computeChangeSet(
                        $em->getClassMetadata(ClassUtils::getClass($entity)),
                        $entity
                    );
                }
            }
        }
    }
}
