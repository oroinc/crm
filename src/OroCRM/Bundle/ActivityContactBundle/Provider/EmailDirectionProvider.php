<?php

namespace OroCRM\Bundle\ActivityContactBundle\Provider;

use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Model\EmailHolderInterface;

use OroCRM\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;

class EmailDirectionProvider implements DirectionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSupportedClass()
    {
        return 'Oro\Bundle\EmailBundle\Entity\Email';
    }

    /**
     * {@inheritdoc}
     */
    public function getDirection($activity, $target)
    {
        /** @var $activity Email */
        /** @var $target EmailHolderInterface */
        if ($activity->getFromEmailAddress()->getEmail() === $target->getEmail()) {
            return DirectionProviderInterface::DIRECTION_OUTGOING;
        }

        return DirectionProviderInterface::DIRECTION_INCOMING;
    }

    /**
     * {@inheritdoc}
     */
    public function getDate($activity)
    {
        /** @var $activity Email */
        return $activity->getSentAt() ?: new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
