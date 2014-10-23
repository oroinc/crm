<?php

namespace OroCRM\Bundle\TaskBundle\Entity\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;

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
                $this->assignTaskActivity($entity, $em, $uow);
            }
        }

        $changedEntities = $uow->getScheduledEntityUpdates();
        foreach ($changedEntities as $entity) {
            if ($entity instanceof Task) {
                $this->ensureTaskActivityArranged($entity, $em, $uow);
            }
        }
    }

    /**
     * @param Task          $entity
     * @param EntityManager $em
     * @param UnitOfWork    $uow
     */
    protected function assignTaskActivity(Task $entity, EntityManager $em, UnitOfWork $uow)
    {
        $hasChanges = $this->activityManager->addActivityTarget($entity, $entity->getOwner());
        $hasChanges |= $this->activityManager->addActivityTarget($entity, $entity->getReporter());
        // recompute change set if needed
        if ($hasChanges) {
            $uow->computeChangeSet(
                $em->getClassMetadata(ClassUtils::getClass($entity)),
                $entity
            );
        }
    }

    /**
     * @param Task          $entity
     * @param EntityManager $em
     * @param UnitOfWork    $uow
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function ensureTaskActivityArranged(Task $entity, EntityManager $em, UnitOfWork $uow)
    {
        // prepare a list of activity targets
        $changeSet = $uow->getEntityChangeSet($entity);
        $toRemove  = [];
        $toAdd     = [];
        foreach ($changeSet as $field => $values) {
            if ($field === 'owner' || $field === 'reporter') {
                list($oldValue, $newValue) = $values;
                if ($oldValue !== $newValue) {
                    if (!in_array($oldValue, $toRemove, true)) {
                        $toRemove[] = $oldValue;
                    }
                    if (!in_array($newValue, $toAdd, true)) {
                        $toAdd[] = $newValue;
                    }
                }
            }
        }
        // set activity targets
        $hasChanges = false;
        if (!empty($toRemove)) {
            $keys = array_keys($toRemove);
            foreach ($keys as $key) {
                if ($toRemove[$key] === $entity->getOwner() || $toRemove[$key] === $entity->getReporter()) {
                    unset($toRemove[$key]);
                }
            }
            foreach ($toRemove as $target) {
                $hasChanges |= $this->activityManager->removeActivityTarget($entity, $target);
            }
        }
        foreach ($toAdd as $target) {
            $hasChanges |= $this->activityManager->addActivityTarget($entity, $target);
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
