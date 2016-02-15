<?php

namespace OroCRM\Bundle\CallBundle\Placeholder;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\ActivityBundle\Manager\ActivityManager;

use OroCRM\Bundle\CallBundle\Entity\Call;

class LogCallPlaceholderFilter
{
    /** @var Call */
    protected $call;

    /**
     * @var ActivityManager
     */
    protected $activityManager;

    /**
     * @param ActivityManager $activityManager
     */
    public function __construct(ActivityManager $activityManager)
    {
        $this->call  = new Call();
        $this->activityManager = $activityManager;
    }

    /**
     * Check if call log action is applicable to entity as activity
     *
     * @param object|null $entity
     * @return bool
     */
    public function isApplicable($entity = null)
    {
        return
            is_object($entity) &&
            $this->call->supportActivityTarget(ClassUtils::getClass($entity)) &&
            $this->activityManager->hasActivityAssociation(
                ClassUtils::getClass($entity),
                ClassUtils::getClass($this->call)
            );
    }
}
