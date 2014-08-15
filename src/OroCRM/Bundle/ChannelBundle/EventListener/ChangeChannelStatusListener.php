<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\ORM\EntityManager;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Event\ChannelChangeStatusEvent;

class ChangeChannelStatusListener
{
    /** @var RegistryInterface */
    protected $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param ChannelChangeStatusEvent $event
     */
    public function onChannelStatusChange(ChannelChangeStatusEvent $event)
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

            $this->getManager()->persist($integration);
            $this->getManager()->flush();
        }
    }

    /**
     * @return EntityManager
     */
    protected function getManager()
    {
        return $this->registry->getEntityManager();
    }
}
