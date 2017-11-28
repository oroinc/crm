<?php

namespace Oro\Bundle\ChannelBundle\EventListener\ORM;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;

class RefreshChannelCacheListener
{
    /** @var StateProvider  */
    protected $stateProvider;

    /**
     * @param StateProvider $stateProvider
     */
    public function __construct(StateProvider $stateProvider)
    {
        $this->stateProvider = $stateProvider;
    }

    /**
     * @param Channel $channel
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(Channel $channel, LifecycleEventArgs $eventArgs)
    {
        $this->stateProvider->processChannelChange();
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postRemove(Channel $channel, LifecycleEventArgs $eventArgs)
    {
        $this->stateProvider->processChannelChange();
    }
}
