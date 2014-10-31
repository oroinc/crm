<?php

namespace OroCRM\Bundle\CallBundle\Entity\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Oro\Bundle\ActivityBundle\Manager\ActivityManager;

use OroCRM\Bundle\CallBundle\Entity\Call;

class CallActivityManager
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
     * @param Call   $call
     * @param object $target
     *
     * @return bool TRUE if the association was added; otherwise, FALSE
     */
    public function addAssociation(Call $call, $target)
    {
        return $this->activityManager->addActivityTarget($call, $target);
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
            if ($entity instanceof Call) {
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
            if ($entity instanceof Call) {
                $hasChanges = false;
                $changeSet  = $uow->getEntityChangeSet($entity);
                foreach ($changeSet as $field => $values) {
                    if ($field === 'owner') {
                        list($oldValue, $newValue) = $values;
                        if ($oldValue !== $newValue) {
                            $hasChanges |= $this->activityManager->replaceActivityTarget(
                                $entity,
                                $oldValue,
                                $newValue
                            );
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
