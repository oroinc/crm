<?php

namespace OroCRM\Bundle\ActivityContactBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\ActivityBundle\Event\ActivityEvent;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

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
        $direction = $this->activityContactProvider->getActivityDirection($activity, $target);
        if ($direction !== DirectionProviderInterface::DIRECTION_UNKNOWN) {
            $accessor = PropertyAccess::createPropertyAccessor();

            $contactDate = $this->activityContactProvider->getActivityDate($activity);

            $accessor->setValue(
                $target,
                ActivityScope::CONTACT_COUNT,
                ((int)$accessor->getValue($target, ActivityScope::CONTACT_COUNT) + 1)
            );
            $accessor->setValue($target, ActivityScope::LAST_CONTACT_DATE, $contactDate);

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
            $accessor->setValue($target, $contactDatePath, $contactDate);
        }
    }

    /**
     * Collect activities changes
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $entities = $args->getEntityManager()->getUnitOfWork()->getScheduledEntityDeletions();
        if (!empty($entities)) {
            foreach ($entities as $entity) {
                $class = $this->doctrineHelper->getEntityClass($entity);
                $id    = $this->doctrineHelper->getSingleEntityIdentifier($entity);
                if ($this->activityContactProvider->isSupportedEntity($class)
                    && empty($this->deletedEntities[md5($class . $id)])
                ) {
                    $targets     = $entity->getActivityTargetEntities();
                    $targetsInfo = [];
                    foreach ($targets as $target) {
                        $targetsInfo[] = [
                            'class'     => $this->doctrineHelper->getEntityClass($target),
                            'id'        => $this->doctrineHelper->getSingleEntityIdentifier($target),
                            'direction' => $this->activityContactProvider->getActivityDirection($entity, $target)
                        ];
                    }
                    $this->deletedEntities[md5($class . $id)] = [
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
        if (!empty($this->deletedEntities)) {
            $accessor = PropertyAccess::createPropertyAccessor();
            foreach ($this->deletedEntities as $activityData) {
                foreach ($activityData['targets'] as $targetInfo) {
                    $direction = $targetInfo['direction'];
                    $target = $em->getRepository($targetInfo['class'])->find($targetInfo['id']);
                    $accessor->setValue(
                        $target,
                        ActivityScope::CONTACT_COUNT,
                        ((int)$accessor->getValue($target, ActivityScope::CONTACT_COUNT) - 1)
                    );

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
                        ((int)$accessor->getValue($target, $directionCountPath) - 1)
                    );

                    $activityDate = $this->activityContactProvider->getLastContactActivityDate(
                        $em,
                        $target,
                        $activityData['id'],
                        $direction
                    );
                    if ($activityDate) {
                        $accessor->setValue($target, ActivityScope::LAST_CONTACT_DATE, $activityDate['all']);
                        $accessor->setValue($target, $contactDatePath, $activityDate['direction']);
                    }

                    $em->persist($target);
                }
            }

            $this->deletedEntities = [];
            $em->flush();
        }
    }
}
