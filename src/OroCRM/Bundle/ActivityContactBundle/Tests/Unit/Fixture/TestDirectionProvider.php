<?php

namespace OroCRM\Bundle\ActivityContactBundle\Tests\Unit\Fixture;

use Doctrine\ORM\EntityManager;

use OroCRM\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;

class TestDirectionProvider implements DirectionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSupportedClass()
    {
        return 'OroCRM\Bundle\ActivityContactBundle\Tests\Unit\Fixture\TestActivity';
    }

    /**
     * {@inheritdoc}
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
