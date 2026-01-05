<?php

namespace Oro\Bundle\ActivityContactBundle\Direction;

use Doctrine\ORM\EntityManager;

/**
 * Represents a service that provides the direction information for a contact activity.
 */
interface DirectionProviderInterface
{
    public const DIRECTION_INCOMING = 'incoming';
    public const DIRECTION_OUTGOING = 'outgoing';
    public const DIRECTION_UNKNOWN  = 'unknown';
    public const CONTACT_INFORMATION_SCOPE_EMAIL = 'email';

    /**
     * Return direction of activity for target
     *
     * @param object $activity
     * @param object $target
     *
     * @return string
     */
    public function getDirection($activity, $target);

    /**
     * Checks if direction was changed
     *
     * @param array $changeSet
     *
     * @return bool
     */
    public function isDirectionChanged($changeSet = []);

    /**
     * Return activity datetime
     *
     * @param object $activity
     *
     * @return \DateTime
     */
    public function getDate($activity);

    /**
     * Return array of last activities for given target
     *
     * @param EntityManager $em
     * @param object        $target
     * @param string        $direction
     * @param integer       $skipId
     *
     * @return array of dates
     *   - all: Last activity date without regard to the direction
     *   - direction:   Last activity date for given direction
     */
    public function getLastActivitiesDateForTarget(EntityManager $em, $target, $direction, $skipId = null);
}
