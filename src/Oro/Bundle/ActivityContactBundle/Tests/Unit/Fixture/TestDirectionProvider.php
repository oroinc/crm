<?php

namespace Oro\Bundle\ActivityContactBundle\Tests\Unit\Fixture;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;

class TestDirectionProvider implements DirectionProviderInterface
{
    /**
     * {@inheritdoc}
     * @param TestActivity $activity
     */
    public function getDirection($activity, $target)
    {
        return $activity->getDirection();
    }

    /**
     * {@inheritdoc}
     */
    public function isDirectionChanged($changeSet = [])
    {
        return false;
    }

    /**
     * {@inheritdoc}
     * @param TestActivity $activity
     */
    public function getDate($activity)
    {
        return $activity->getCreated();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastActivitiesDateForTarget(EntityManager $em, $target, $direction, $skipId = null)
    {
        return [
            'all'       => $target->getCreated(),
            'direction' => $target->getCreated()
        ];
    }
}
