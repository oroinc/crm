<?php

namespace Oro\Bundle\ActivityContactBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Oro\Bundle\ActivityBundle\Event\ActivityEvent;
use Oro\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use Oro\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use Oro\Bundle\ActivityContactBundle\Model\TargetExcludeList;
use Oro\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;
use Oro\Bundle\ActivityContactBundle\Tools\ActivityListenerChangedTargetsBag;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Recalculates and updates contact information, depends on 'activity' actions.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
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

    protected ?ActivityListenerChangedTargetsBag $changedTargetsBag = null;

    public function __construct(
        ActivityContactProvider $activityContactProvider,
        DoctrineHelper $doctrineHelper,
        ConfigManager $configManager
    ) {
        $this->activityContactProvider = $activityContactProvider;
        $this->doctrineHelper          = $doctrineHelper;
        $this->configManager           = $configManager;
    }

    public function setChangedTargetsBag(ActivityListenerChangedTargetsBag $changedTargetsBag): void
    {
        $this->changedTargetsBag = $changedTargetsBag;
    }

    /**
     * Recalculate activity contacts on add new activity to the target
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

    public function onRemoveActivity(ActivityEvent $event)
    {
        $activity = $event->getActivity();
        $target = $event->getTarget();
        $extendProvider  = $this->configManager->getProvider('extend');
        $targetClassName = ClassUtils::getClass($target);

        $direction = $this->activityContactProvider->getActivityDirection($activity, $target);
        if ($direction === DirectionProviderInterface::DIRECTION_UNKNOWN) {
            return;
        }

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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $entitiesToDelete = $args->getEntityManager()->getUnitOfWork()->getScheduledEntityDeletions();
        $entitiesToUpdate = $args->getEntityManager()->getUnitOfWork()->getScheduledEntityUpdates();
        $extendProvider   = $this->configManager->getProvider('extend');
        if (!empty($entitiesToDelete) || !empty($entitiesToUpdate)) {
            foreach ($entitiesToDelete as $entity) {
                $class = $this->doctrineHelper->getEntityClass($entity);
                if (!$this->isSupportedClass($class)) {
                    continue;
                }

                $id    = $this->doctrineHelper->getSingleEntityIdentifier($entity);
                $key   = $class . '_' . $id;
                if (!isset($this->deletedEntities[$key])
                    && $this->activityContactProvider->isSupportedEntity($class)
                ) {
                    $targets     = $entity->getActivityTargets();
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

            $this->changedTargetsBag->add($entitiesToUpdate, $args->getEntityManager()->getUnitOfWork());

            foreach ($entitiesToUpdate as $entity) {
                $class = $this->doctrineHelper->getEntityClass($entity);
                if (!$this->isSupportedClass($class)) {
                    continue;
                }

                $id    = $this->doctrineHelper->getSingleEntityIdentifier($entity);
                $key   = $class . '_' . $id;
                if (!isset($this->updatedEntities[$key])
                    && $this->activityContactProvider->isSupportedEntity($class)
                ) {
                    $changes            = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($entity);
                    $isDirectionChanged = $this->activityContactProvider
                        ->getActivityDirectionProvider($entity)
                        ->isDirectionChanged($changes);

                    $targets     = $entity->getActivityTargets();
                    $targetsInfo = [];
                    foreach ($targets as $target) {
                        if (!$this->changedTargetsBag->isChanged($target)) {
                            continue;
                        }

                        $targetClassName = ClassUtils::getClass($target);
                        if (!TargetExcludeList::isExcluded($targetClassName) &&
                            $extendProvider->getConfig($targetClassName)->is('is_extend')) {
                            $targetsInfo[] = [
                                'class' => $this->doctrineHelper->getEntityClass($target),
                                'id' => $this->doctrineHelper->getSingleEntityIdentifier($target),
                                'direction' => $this->activityContactProvider->getActivityDirection($entity, $target),
                            ];
                        }
                    }
                    $this->updatedEntities[$key] = [
                        'class'       => $class,
                        'id'          => $id,
                        'contactDate' => $this->activityContactProvider->getActivityDate($entity),
                        'targets'     => $targetsInfo
                    ];

                    if ($isDirectionChanged) {
                        $this->changeTargetsDirections($entity, $targets, $args->getEntityManager());
                    }
                }
            }
        }
    }

    /**
     * Save collected changes
     *
     * @throws \Exception
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $entityManager = $args->getEntityManager();
        if (empty($this->deletedEntities) && empty($this->updatedEntities)) {
            return;
        }

        $entityManager->beginTransaction();
        try {
            $accessor = PropertyAccess::createPropertyAccessor();
            $this->processDeletedEntities($entityManager, $accessor);
            $this->processUpdatedEntities($entityManager, $accessor);

            $entityManager->flush();
            $entityManager->commit();
        } catch (\Exception $e) {
            $entityManager->rollback();
            throw $e;
        }
    }

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

    protected function processUpdatedEntities(EntityManager $em, PropertyAccessorInterface $accessor)
    {
        foreach ($this->updatedEntities as $activityData) {
            foreach ($activityData['targets'] as $targetInfo) {
                $direction          = $targetInfo['direction'];
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

                $em->persist($target);
            }
        }

        $this->updatedEntities = [];
        $this->changedTargetsBag->clear();
    }

    protected function changeTargetsDirections(object $activity, array $targets, EntityManager $em): void
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        /** process counts (in case direction was changed) */
        foreach ($targets as $target) {
            $activityDirection = $this->activityContactProvider->getActivityDirection($activity, $target);

            list($oldDirection, $newDirection) = $this->getDirectionProperties($activityDirection, true);
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

            $em->persist($target);
        }
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

    /**
     * @param string $class
     * @return bool
     */
    protected function isSupportedClass($class)
    {
        $entityIdentifiersByClassName = $this->doctrineHelper->getEntityIdentifierFieldNamesForClass($class);

        return count($entityIdentifiersByClassName) === 1;
    }
}
