<?php

namespace OroCRM\Bundle\CallBundle\Provider;

use OroCRM\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use OroCRM\Bundle\CallBundle\Entity\Call;

class CallDirectionProvider implements DirectionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSupportedClass()
    {
        return 'OroCRM\Bundle\CallBundle\Entity\Call';
    }

    /**
     * {@inheritdoc}
     */
    public function getDirection($activity, $target)
    {
        /** @var $activity Call */
        return $activity->getDirection()->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getDate($activity)
    {
        /** @var $activity Call */
        return $activity->getCreatedAt() ?: new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
