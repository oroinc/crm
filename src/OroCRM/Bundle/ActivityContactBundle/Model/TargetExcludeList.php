<?php

namespace OroCRM\Bundle\ActivityContactBundle\Model;

class TargetExcludeList
{
    /**
     * To skipp User and activities entities recalculate to hot fix bug CRM-4767
     *
     * @var array
     */
    protected static $excludeTargets = [
        'Oro\Bundle\UserBundle\Entity\User',
        'OroCRM\Bundle\TaskBundle\Entity\Task',
        'Oro\Bundle\CalendarBundle\Entity\CalendarEvent',
        'OroCRM\Bundle\CallBundle\Entity\Call',
        'Oro\Bundle\EmailBundle\Entity\Email',
    ];

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
