<?php

namespace OroCRM\Bundle\ActivityContactBundle\Provider;

use Doctrine\Common\Util\ClassUtils;

use OroCRM\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;

class ActivityContactProvider
{
    /** @var DirectionProviderInterface[] */
    protected $providers;

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
     * Get contact activity direction provider
     *
     * @param $activity
     * @return bool|DirectionProviderInterface
     */
    protected function getActivityDirectionProvider($activity)
    {
        $activityClass = ClassUtils::getClass($activity);
        if (in_array($activityClass, array_keys($this->providers))) {
            return $this->providers[$activityClass];
        }

        return false;
    }
}
