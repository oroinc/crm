<?php

namespace OroCRM\Bundle\ActivityContactBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use Oro\Bundle\ActivityBundle\Event\ActivityEvent;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

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

    /** @var ConfigManager */
    protected $configManager;

    /**
     * @param ActivityContactProvider $activityContactProvider
     * @param DoctrineHelper          $doctrineHelper
     * @param ConfigManager           $configManager
     */
    public function __construct(
        ActivityContactProvider $activityContactProvider,
        DoctrineHelper $doctrineHelper,
        ConfigManager $configManager
    ) {
        $this->activityContactProvider = $activityContactProvider;
        $this->doctrineHelper          = $doctrineHelper;
        $this->configManager           = $configManager;
    }

    /**
     * Recalculate activity contacts on add new activity to the target
     *
     * @param ActivityEvent $event
     */
    public function onAddActivity(ActivityEvent $event)
    {
        $activity        = $event->getActivity();
        $target          = $event->getTarget();
        $extendProvider  = $this->configManager->getProvider('extend');
        $targetClassName = ClassUtils::getClass($target);

        if (TargetExcludeList::isExcluded($targetClassName) ||
            !$extendProvider->getConfig($targetClassName)->is('is_extend')) {
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
     * @param ActivityEvent $event
     */
    public function onRemoveActivity(ActivityEvent $event)
    {
        $activity = $event->getActivity();
        $target = $event->getTarget();
        $extendProvider  = $this->configManager->getProvider('extend');
        $targetClassName = ClassUtils::getClass($target);

        if (TargetExcludeList::isExcluded($targetClassName) ||
            !$extendProvider->getConfig($targetClassName)->is('is_extend')) {
            return;
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        $accessor->setValue(
            $target,
            ActivityScope::CONTACT_COUNT,
            (int)$accessor->getValue($target, ActivityScope::CONTACT_COUNT) - 1
        );

        $direction = $this->activityContactProvider->getActivityDirection($activity, $target);
        list($directionProperty) = $this->getDirectionProperties($direction);
        $accessor->setValue(
            $target,
            $directionProperty,
            (int)$accessor->getValue($target, $directionProperty) - 1
        );
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
        $extendProvider   = $this->configManager->getProvider('extend');
        if (!empty($entitiesToDelete) || !empty($entitiesToUpdate)) {
            foreach ($entitiesToDelete as $entity) {
                $class = $this->doctrineHelper->getEntityClass($entity);
                $id    = $this->doctrineHelper->getSingleEntityIdentifier($entity);
                $key   = $class . '_' . $id;
                if (!isset($this->deletedEntities[$key])
                    && $this->activityContactProvider->isSupportedEntity($class)
                ) {
                    $targets     = $entity->getActivityTargetEntities();
                    $targetsInfo = [];
                    foreach ($targets as $target) {
                        $targetClassName = ClassUtils::getClass($target);
                        if (!TargetExcludeList::isExcluded($targetClassName) &&
                            $extendProvider->getConfig($targetClassName)->is('is_extend')) {
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
                if (!isset($this->updatedEntities[$key])
                    && $this->activityContactProvider->isSupportedEntity($class)
                ) {
                    $changes            = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($entity);
                    $isDirectionChanged = $this->activityContactProvider
                        ->getActivityDirectionProvider($entity)
                        ->isDirectionChanged($changes);

                    $targets     = $entity->getActivityTargetEntities();
                    $targetsInfo = [];
                    foreach ($targets as $target) {
                        $targetClassName = ClassUtils::getClass($target);
                        if (!TargetExcludeList::isExcluded($targetClassName) &&
                            $extendProvider->getConfig($targetClassName)->is('is_extend')) {
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
        if (empty($this->deletedEntities) && empty($this->updatedEntities)) {
            return;
        }

        $accessor = PropertyAccess::createPropertyAccessor();
        $this->processDeletedEntities($em, $accessor);
        $this->processUpdatedEntities($em, $accessor);

        $em->flush();
    }

    /**
     * @param EntityManager $em
     * @param PropertyAccessorInterface $accessor
     */
    protected function processDeletedEntities(EntityManager $em, PropertyAccessorInterface $accessor)
    {
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

        $this->deletedEntities = [];
    }

    /**
     * @param EntityManager $em
     * @param PropertyAccessorInterface $accessor
     */
    protected function processUpdatedEntities(EntityManager $em, PropertyAccessorInterface $accessor)
    {
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
                    list($oldDirection, $newDirection) = $this->getDirectionProperties($direction, $isDirectionChanged);
                    $accessor->setValue(
                        $target,
                        $newDirection,
                        (int)$accessor->getValue($target, $newDirection) + 1
                    );
                    $accessor->setValue(
                        $target,
                        $oldDirection,
                        (int)$accessor->getValue($target, $oldDirection) - 1
                    );
                }

                $em->persist($target);
            }
        }

        $this->updatedEntities = [];
    }

    /**
     * @param string $currentDirection
     * @param bool $isDirectionChanged
     *
     * @return array Where first value is old property and second is new
     */
    protected function getDirectionProperties($currentDirection, $isDirectionChanged = false)
    {
        if ($isDirectionChanged) {
            $properties = [
                ActivityScope::CONTACT_COUNT_IN,
                ActivityScope::CONTACT_COUNT_OUT,
            ];

            return $currentDirection === DirectionProviderInterface::DIRECTION_INCOMING
                ? array_reverse($properties)
                : $properties;
        }

        return $currentDirection === DirectionProviderInterface::DIRECTION_INCOMING
            ? [ActivityScope::CONTACT_COUNT_IN, ActivityScope::CONTACT_COUNT_IN]
            : [ActivityScope::CONTACT_COUNT_OUT, ActivityScope::CONTACT_COUNT_OUT];
    }
}
