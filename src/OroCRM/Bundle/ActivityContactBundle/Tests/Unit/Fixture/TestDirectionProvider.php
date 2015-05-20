<?php

namespace OroCRM\Bundle\ActivityContactBundle\Tests\Unit\Fixture;

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
    public function getDate($activity)
    {
        return $activity->getCreated();
    }
}
