<?php

namespace OroCRM\Bundle\ActivityContactBundle\EventListener;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\ActivityBundle\Event\ActivityEvent;

use OroCRM\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use OroCRM\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use OroCRM\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;

class ActivityListener
{
    /** @var ActivityContactProvider */
    protected $activityContactProvider;

    /**
     * @param ActivityContactProvider $activityContactProvider
     */
    public function __construct(ActivityContactProvider $activityContactProvider)
    {
        $this->activityContactProvider = $activityContactProvider;
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
}
