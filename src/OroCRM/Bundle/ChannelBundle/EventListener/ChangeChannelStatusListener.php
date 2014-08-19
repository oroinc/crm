<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\ORM\EntityManager;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use OroCRM\Bundle\ChannelBundle\Event\AbstractEvent;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

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
     * @param AbstractEvent $event
     */
    public function onChannelStatusChange(AbstractEvent $event)
    {
        /** @var Channel $channel */
        $channel    = $event->getChannel();
        $dataSource = $channel->getDataSource();

        if ($dataSource instanceof Integration) {
            $dataSource->setEnabled(Channel::STATUS_ACTIVE === $channel->getStatus());

            $this->getManager()->persist($dataSource);
            $this->getManager()->flush();
        }
    }

    /**
     * @return EntityManager
     */
    protected function getManager()
    {
        return $this->registry->getManager();
    }
}
