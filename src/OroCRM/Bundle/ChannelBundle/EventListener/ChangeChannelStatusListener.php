<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Event\ChannelChangeStatusEvent;

class ChangeChannelStatusListener
{
    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param ChannelChangeStatusEvent $event
     */
    public function onChannelStatusChanging(ChannelChangeStatusEvent $event)
    {
        /** @var Channel $channel */
        $channel     = $event->getChannel();
        $integration = $channel->getDataSource();

        if ($integration instanceof Integration) {
            if (Channel::STATUS_ACTIVE === $channel->getStatus()) {
                $integration->setEnabled(true);
            } elseif (Channel::STATUS_INACTIVE === $channel->getStatus()) {
                $integration->setEnabled(false);
            }

            $this->em->persist($integration);
            $this->em->flush();
        }
    }
}
