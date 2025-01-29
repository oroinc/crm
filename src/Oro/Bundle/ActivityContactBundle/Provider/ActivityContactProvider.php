<?php

namespace Oro\Bundle\ActivityContactBundle\Provider;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use Psr\Container\ContainerInterface;

/**
 * The registry for providers of the direction information for contact activities.
 */
class ActivityContactProvider
{
    /** @var string[] */
    private $supportedClasses;

    /** @var ContainerInterface */
    private $providers;

    private ActivityManager $activityManager;

    /**
     * @param string[]           $supportedClasses
     * @param ContainerInterface $providers
     */
    public function __construct(array $supportedClasses, ContainerInterface $providers)
    {
        $this->supportedClasses = $supportedClasses;
        $this->providers = $providers;
    }

    /**
     * Gets a direction of the given activity.
     *
     * @param object $activity
     * @param object $target
     *
     * @return string
     */
    public function getActivityDirection($activity, $target)
    {
        $provider = $this->getActivityDirectionProvider($activity);
        if ($provider) {
            return $provider->getDirection($activity, $target);
        }

        return DirectionProviderInterface::DIRECTION_UNKNOWN;
    }

    /**
     * Gets a contact date for the given activity.
     *
     * @param object $activity
     *
     * @return \DateTime|null
     */
    public function getActivityDate($activity)
    {
        $provider = $this->getActivityDirectionProvider($activity);
        if ($provider) {
            return $provider->getDate($activity);
        }

        return null;
    }

    /**
     * Gets the list of supported activity classes.
     *
     * @return string[]
     */
    public function getSupportedActivityClasses()
    {
        return $this->supportedClasses;
    }

    /**
     * Checks if the given entity class supports a direction information.
     *
     * @param string $activityClass
     *
     * @return bool
     */
    public function isSupportedEntity($activityClass)
    {
        return in_array($activityClass, $this->supportedClasses, true);
    }

    /**
     * Gets an array contains last dates for given target entity.
     *
     * @param EntityManager $em
     * @param object        $targetEntity
     * @param string        $direction
     * @param integer       $skippedId
     * @param string        $class
     *
     * @return array ['all' => date, 'direction' => date]
     *   - all: Last activity date without regard to the direction
     *   - direction: Last activity date for given direction
     */
    public function getLastContactActivityDate(
        EntityManager $em,
        $targetEntity,
        $direction,
        $skippedId = null,
        $class = null
    ) {
        $allDate = null;
        $directionDate  = null;
        $allDates = [];
        $directionDates = [];
        $targetClass = ClassUtils::getClass($targetEntity);

        foreach ($this->supportedClasses as $supportedClass) {
            $skippedId = ($skippedId && $supportedClass === $class) ? $skippedId : null;
            /** @var DirectionProviderInterface $provider */
            $provider = $this->providers->get($supportedClass);

            $lastActivitiesDateForTarget = [];
            if ($this->activityManager->hasActivityAssociation($targetClass, $supportedClass)) {
                $lastActivitiesDateForTarget = $provider->getLastActivitiesDateForTarget(
                    $em,
                    $targetEntity,
                    $direction,
                    $skippedId
                );
            }

            if (!empty($lastActivitiesDateForTarget)) {
                $allDates[] = $lastActivitiesDateForTarget['all'];
                if ($lastActivitiesDateForTarget['direction']) {
                    $directionDates[] = $lastActivitiesDateForTarget['direction'];
                }
            }
        }

        if ($allDates) {
            $allDate = $this->getMaxDate($allDates);
        }

        if ($directionDates) {
            $directionDate = $this->getMaxDate($directionDates);
        }

        return ['all' => $allDate, 'direction' => $directionDate];
    }

    /**
     * Gets a direction provider for the given contact activity.
     *
     * @param object $activity
     *
     * @return DirectionProviderInterface|null
     */
    public function getActivityDirectionProvider($activity)
    {
        $activityClass = ClassUtils::getClass($activity);
        if (!in_array($activityClass, $this->supportedClasses, true)) {
            return null;
        }

        return $this->providers->get($activityClass);
    }

    /**
     * Extracts the max date from the array of dates.
     *
     * @param \DateTime[] $datesArray
     *
     * @return \DateTime
     */
    private function getMaxDate($datesArray)
    {
        $result = null;
        foreach ($datesArray as $date) {
            if (null === $result || $date->getTimestamp() > $result->getTimestamp()) {
                $result = $date;
            }
        }

        return $result;
    }

    public function setActivityManager(ActivityManager $activityManager): void
    {
        $this->activityManager = $activityManager;
    }
}
