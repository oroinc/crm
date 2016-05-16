<?php

namespace OroCRM\Bundle\ActivityContactBundle\Provider;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

use OroCRM\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;

class ActivityContactProvider
{
    /** @var DirectionProviderInterface[] */
    protected $providers;

    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param DirectionProviderInterface $provider
     */
    public function addProvider(DirectionProviderInterface $provider)
    {
        $this->providers[$provider->getSupportedClass()] = $provider;
    }

    /**
     * Return direction of given activity.
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
     * Get contact date
     *
     * @param $activity
     *
     * @return bool
     */
    public function getActivityDate($activity)
    {
        $provider = $this->getActivityDirectionProvider($activity);
        if ($provider) {
            return $provider->getDate($activity);
        }

        return false;
    }


    /**
     * Return list of supported activity classes
     *
     * @return array
     */
    public function getSupportedActivityClasses()
    {
        return array_keys($this->providers);
    }

    /**
     * Check if given entity class is supports
     *
     * @param $activityClass
     *
     * @return bool
     */
    public function isSupportedEntity($activityClass)
    {
        return isset($this->providers[$activityClass]);
    }

    /**
     * Get array with all and direction dates for given target
     *
     * @param object        $targetEntity
     * @param string        $direction
     * @param integer       $skippedId
     * @param string        $class
     *
     * @return array of all and direction dates
     *   - all: Last activity date without regard to the direction
     *   - direction: Last activity date for given direction
     */
    public function getLastContactActivityDate(
        $targetEntity,
        $direction,
        $skippedId = null,
        $class = null
    ) {
        $allDate        = null;
        $directionDate  = null;
        $allDates       = [];
        $directionDates = [];
        foreach ($this->providers as $supportedClass => $provider) {
            $skippedId = ($skippedId && $supportedClass === $class) ? $skippedId : null;
            $result    = $provider->getLastActivitiesDateForTarget($targetEntity, $direction, $skippedId);
            if (!empty($result)) {
                $allDates[] = $result['all'];
                if ($result['direction']) {
                    $directionDates[] = $result['direction'];
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
     * Get max date from the array of dates
     *
     * @param \DateTime[] $datesArray
     *
     * @return \DateTime
     */
    protected function getMaxDate($datesArray)
    {
        if (count($datesArray) > 1) {
            usort(
                $datesArray,
                function (\DateTime $a, \DateTime $b) {
                    $firstStamp  = $a->getTimestamp();
                    $secondStamp = $b->getTimestamp();
                    if ($firstStamp === $secondStamp) {
                        return 0;
                    }

                    return ($firstStamp > $secondStamp) ? -1 : 1;
                }
            );
        }

        return array_shift($datesArray);
    }

    /**
     * Get contact activity direction provider
     *
     * @param $activity
     *
     * @return bool|DirectionProviderInterface
     */
    public function getActivityDirectionProvider($activity)
    {
        $activityClass = ClassUtils::getClass($activity);
        if (isset($this->providers[$activityClass])) {
            return $this->providers[$activityClass];
        }

        return false;
    }
}
