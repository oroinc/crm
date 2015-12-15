<?php

namespace OroCRM\Bundle\ActivityContactBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\ActivityBundle\Event\ActivityEvent;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

use OroCRM\Bundle\ActivityContactBundle\Model\TargetExcludeList;
use OroCRM\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use OroCRM\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use OroCRM\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;

class ActivityListener
{
    /** @var ActivityContactProvider */
    protected $activityContactProvider;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var array */
    protected $deletedEntities = [];

    /** @var array */
    protected $updatedEntities = [];

    /**
     * @param ActivityContactProvider $activityContactProvider
     * @param DoctrineHelper          $doctrineHelper
     */
    public function __construct(ActivityContactProvider $activityContactProvider, DoctrineHelper $doctrineHelper)
    {
        $this->activityContactProvider = $activityContactProvider;
        $this->doctrineHelper          = $doctrineHelper;
    }

    /**
     * Recalculate activity contacts on add new activity to the target
     *
     * @param ActivityEvent $event
     */
    public function onAddActivity(ActivityEvent $event)
    {
        $activity  = $event->getActivity();
        $target    = $event->getTarget();

        if (TargetExcludeList::isExcluded(ClassUtils::getClass($target))) {
            return;
        }

        $direction = $this->activityContactProvider->getActivityDirection($activity, $target);
        if ($direction !== DirectionProviderInterface::DIRECTION_UNKNOWN) {
            $accessor = PropertyAccess::createPropertyAccessor();

            $contactDate = $this->activityContactProvider->getActivityDate($activity);

            $accessor->setValue(
                $target,
                ActivityScope::CONTACT_COUNT,
                ((int)$accessor->getValue($target, ActivityScope::CONTACT_COUNT) + 1)
            );

            $lastContactDate = $accessor->getValue($target, ActivityScope::LAST_CONTACT_DATE);
            if ($lastContactDate < $contactDate) {
                $accessor->setValue($target, ActivityScope::LAST_CONTACT_DATE, $contactDate);
            }

            if ($direction === DirectionProviderInterface::DIRECTION_INCOMING) {
                $directionCountPath = ActivityScope::CONTACT_COUNT_IN;
                $contactDatePath    = ActivityScope::LAST_CONTACT_DATE_IN;
            } else {
                $directionCountPath = ActivityScope::CONTACT_COUNT_OUT;
                $contactDatePath    = ActivityScope::LAST_CONTACT_DATE_OUT;
            }

            $accessor->setValue(
                $target,
                $directionCountPath,
                ((int)$accessor->getValue($target, $directionCountPath) + 1)
            );

            $lastContactDate = $accessor->getValue($target, $contactDatePath);
            if ($lastContactDate < $contactDate) {
                $accessor->setValue($target, $contactDatePath, $contactDate);
            }
        }
    }

    /**
     * Collect activities changes
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $entitiesToDelete = $args->getEntityManager()->getUnitOfWork()->getScheduledEntityDeletions();
        $entitiesToUpdate = $args->getEntityManager()->getUnitOfWork()->getScheduledEntityUpdates();
        if (!empty($entitiesToDelete) || !empty($entitiesToUpdate)) {
            foreach ($entitiesToDelete as $entity) {
                $class = $this->doctrineHelper->getEntityClass($entity);
                $id    = $this->doctrineHelper->getSingleEntityIdentifier($entity);
                $key   = $class . '_' . $id;
                if ($this->activityContactProvider->isSupportedEntity($class) && !isset($this->deletedEntities[$key])) {
                    $targets     = $entity->getActivityTargetEntities();
                    $targetsInfo = [];
                    foreach ($targets as $target) {
                        if (!TargetExcludeList::isExcluded(ClassUtils::getClass($target))) {
                            $targetsInfo[] = [
                                'class' => $this->doctrineHelper->getEntityClass($target),
                                'id' => $this->doctrineHelper->getSingleEntityIdentifier($target),
                                'direction' => $this->activityContactProvider->getActivityDirection($entity, $target)
                            ];
                        }
                    }
                    $this->deletedEntities[$key] = [
                        'class'       => $class,
                        'id'          => $id,
                        'contactDate' => $this->activityContactProvider->getActivityDate($entity),
                        'targets'     => $targetsInfo
                    ];
                }
            }

            foreach ($entitiesToUpdate as $entity) {
                $class = $this->doctrineHelper->getEntityClass($entity);
                $id    = $this->doctrineHelper->getSingleEntityIdentifier($entity);
                $key   = $class . '_' . $id;
                if ($this->activityContactProvider->isSupportedEntity($class)
                    && !isset($this->updatedEntities[$key])
                ) {
                    $changes            = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($entity);
                    $isDirectionChanged = $this->activityContactProvider
                        ->getActivityDirectionProvider($entity)
                        ->isDirectionChanged($changes);

                    $targets     = $entity->getActivityTargetEntities();
                    $targetsInfo = [];
                    foreach ($targets as $target) {
                        if (!TargetExcludeList::isExcluded(ClassUtils::getClass($target))) {
                            $targetsInfo[] = [
                                'class' => $this->doctrineHelper->getEntityClass($target),
                                'id' => $this->doctrineHelper->getSingleEntityIdentifier($target),
                                'direction' => $this->activityContactProvider->getActivityDirection($entity, $target),
                                'is_direction_changed' => $isDirectionChanged
                            ];
                        }
                    }
                    $this->updatedEntities[$key] = [
                        'class'       => $class,
                        'id'          => $id,
                        'contactDate' => $this->activityContactProvider->getActivityDate($entity),
                        'targets'     => $targetsInfo
                    ];
                }
            }
        }
    }

    /**
     * Save collected changes
     *
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        if (!empty($this->deletedEntities) || !empty($this->updatedEntities)) {
            $accessor = PropertyAccess::createPropertyAccessor();
            /** process deleted entities */
            foreach ($this->deletedEntities as $activityData) {
                foreach ($activityData['targets'] as $targetInfo) {
                    $direction = $targetInfo['direction'];
                    $target    = $em->getRepository($targetInfo['class'])->find($targetInfo['id']);
                    $accessor->setValue(
                        $target,
                        ActivityScope::CONTACT_COUNT,
                        ((int)$accessor->getValue($target, ActivityScope::CONTACT_COUNT) - 1)
                    );

                    $directionCountPath = ActivityScope::CONTACT_COUNT_OUT;
                    $contactDatePath    = ActivityScope::LAST_CONTACT_DATE_OUT;
                    if ($direction === DirectionProviderInterface::DIRECTION_INCOMING) {
                        $directionCountPath = ActivityScope::CONTACT_COUNT_IN;
                        $contactDatePath    = ActivityScope::LAST_CONTACT_DATE_IN;
                    }

                    $accessor->setValue(
                        $target,
                        $directionCountPath,
                        ((int)$accessor->getValue($target, $directionCountPath) - 1)
                    );

                    $activityDate = $this->activityContactProvider->getLastContactActivityDate(
                        $em,
                        $target,
                        $direction,
                        $activityData['id'],
                        $activityData['class']
                    );
                    if ($activityDate) {
                        $accessor->setValue($target, ActivityScope::LAST_CONTACT_DATE, $activityDate['all']);
                        $accessor->setValue($target, $contactDatePath, $activityDate['direction']);
                    }

                    $em->persist($target);
                }
            }

            /** process updated entities */
            foreach ($this->updatedEntities as $activityData) {
                foreach ($activityData['targets'] as $targetInfo) {
                    $direction          = $targetInfo['direction'];
                    $isDirectionChanged = $targetInfo['is_direction_changed'];
                    $target             = $em->getRepository($targetInfo['class'])->find($targetInfo['id']);
                    /** process dates */
                    if ($direction === DirectionProviderInterface::DIRECTION_INCOMING) {
                        $contactDatePath         = ActivityScope::LAST_CONTACT_DATE_IN;
                        $oppositeContactDatePath = ActivityScope::LAST_CONTACT_DATE_OUT;
                        $oppositeDirection       = DirectionProviderInterface::DIRECTION_OUTGOING;
                    } else {
                        $contactDatePath         = ActivityScope::LAST_CONTACT_DATE_OUT;
                        $oppositeContactDatePath = ActivityScope::LAST_CONTACT_DATE_IN;
                        $oppositeDirection       = DirectionProviderInterface::DIRECTION_INCOMING;
                    }

                    $lastActivityDate = $this->activityContactProvider
                        ->getLastContactActivityDate($em, $target, $direction);
                    if ($lastActivityDate) {
                        $accessor->setValue($target, ActivityScope::LAST_CONTACT_DATE, $lastActivityDate['all']);
                        $accessor->setValue($target, $contactDatePath, $lastActivityDate['direction']);
                        $accessor->setValue(
                            $target,
                            $oppositeContactDatePath,
                            $this->activityContactProvider->getLastContactActivityDate(
                                $em,
                                $target,
                                $oppositeDirection
                            )['direction']
                        );
                    }
                    /** process counts (in case direction was changed) */
                    if ($isDirectionChanged) {
                        $increment = ActivityScope::CONTACT_COUNT_OUT;
                        $decrement = ActivityScope::CONTACT_COUNT_IN;
                        if ($direction === DirectionProviderInterface::DIRECTION_INCOMING) {
                            $increment = ActivityScope::CONTACT_COUNT_IN;
                            $decrement = ActivityScope::CONTACT_COUNT_OUT;
                        }
                        $accessor->setValue($target, $increment, ((int)$accessor->getValue($target, $increment) + 1));
                        $accessor->setValue($target, $decrement, ((int)$accessor->getValue($target, $decrement) - 1));
                    }

                    $em->persist($target);
                }
            }

            $this->deletedEntities = [];
            $this->updatedEntities = [];

            $em->flush();
        }
    }
}
