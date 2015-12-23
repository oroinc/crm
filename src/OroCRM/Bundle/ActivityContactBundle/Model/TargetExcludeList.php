<?php

namespace OroCRM\Bundle\ActivityContactBundle\Model;

class TargetExcludeList
{
    /**
     * To skipp User entity recalculate to hot fix bug CRM-4767
     *
     * @var array
     */
    protected static $excludeTargets = ['Oro\Bundle\UserBundle\Entity\User'];

    /**
     * @param string $className
     *
     * @return bool
     */
    public static function isExcluded($className)
    {
        return in_array($className, self::$excludeTargets, true);
    }
}
