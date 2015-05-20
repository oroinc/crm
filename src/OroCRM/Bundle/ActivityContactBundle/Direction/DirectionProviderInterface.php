<?php

namespace OroCRM\Bundle\ActivityContactBundle\Direction;

interface DirectionProviderInterface
{
    const DIRECTION_INCOMING = 'incoming';
    const DIRECTION_OUTGOING = 'outgoing';
    const DIRECTION_UNKNOWN  = 'unknown';

    /**
     * Return supported activity entity class name
     *
     * @return string
     */
    public function getSupportedClass();

    /**
     * Return direction of activity for target
     *
     * @param object $activity
     * @param object $target
     * @return string
     */
    public function getDirection($activity, $target);

    /**
     * Return activity datetime
     *
     * @param $activity
     * @return \DateTime
     */
    public function getDate($activity);
}
