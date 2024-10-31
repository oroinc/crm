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
     */
    public function getActivityDirection(object $activity, object $target): string
    {
        $provider = $this->getActivityDirectionProvider($activity);

        return null !== $provider
            ? $provider->getDirection($activity, $target)
            : DirectionProviderInterface::DIRECTION_UNKNOWN;
    }

    /**
     * Gets a contact date for the given activity.
     */
    public function getActivityDate(object $activity): ?\DateTimeInterface
    {
        return $this->getActivityDirectionProvider($activity)?->getDate($activity);
    }

    /**
     * Gets the list of supported activity classes.
     *
     * @return string[]
     */
    public function getSupportedActivityClasses(): array
    {
        return $this->supportedClasses;
    }

    /**
     * Checks if the given entity class supports a direction information.
     */
    public function isSupportedEntity(string $activityClass): bool
    {
        return \in_array($activityClass, $this->supportedClasses, true);
    }

    /**
     * Gets an array contains last dates for given target entity.
     *
     * @param EntityManager $em
     * @param object        $targetEntity
     * @param string        $direction
     * @param int|null      $skippedId
     * @param string|null   $class
     *
     * @return array ['all' => date, 'direction' => date]
     *   - all: Last activity date without regard to the direction
     *   - direction: Last activity date for given direction
     */
    public function getLastContactActivityDate(
        EntityManager $em,
        object $targetEntity,
        string $direction,
        int $skippedId = null,
        string $class = null
    ): array {
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
     */
    public function getActivityDirectionProvider(object $activity): ?DirectionProviderInterface
    {
        $activityClass = ClassUtils::getClass($activity);

        return \in_array($activityClass, $this->supportedClasses, true)
            ? $this->providers->get($activityClass)
            : null;
    }

    /**
     * Extracts the max date from the array of dates.
     */
    private function getMaxDate(array $dates): \DateTimeInterface
    {
        $result = null;
        foreach ($dates as $date) {
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
