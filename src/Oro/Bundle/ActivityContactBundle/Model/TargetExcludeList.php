<?php

namespace Oro\Bundle\ActivityContactBundle\Model;

use Oro\Bundle\ActivityBundle\Model\ActivityInterface;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Contains a list of classes and interfaces for which activity counts should not be tracked.
 */
class TargetExcludeList
{
    /**
     * @var array
     */
    protected static $excludeTargets = [
        ActivityInterface::class,
        User::class
    ];

    public static function addExcludedTarget(string $className)
    {
        self::$excludeTargets[] = $className;
    }

    /**
     * @param string $className
     *
     * @return bool
     */
    public static function isExcluded($className)
    {
        if (in_array($className, self::$excludeTargets, true)) {
            return true;
        }

        foreach (self::$excludeTargets as $excludedTarget) {
            if (is_subclass_of($className, $excludedTarget)) {
                return true;
            }
        }

        return false;
    }
}
