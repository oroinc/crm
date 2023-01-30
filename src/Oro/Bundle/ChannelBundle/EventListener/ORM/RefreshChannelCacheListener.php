<?php

namespace Oro\Bundle\ChannelBundle\EventListener\ORM;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;

/**
 * Listens to Channel events and process channel changes
 */
class RefreshChannelCacheListener
{
    /** @var StateProvider  */
    protected $stateProvider;

    public function __construct(StateProvider $stateProvider)
    {
        $this->stateProvider = $stateProvider;
    }

    public function prePersist(Channel $channel, LifecycleEventArgs $eventArgs)
    {
        $this->stateProvider->processChannelChange();
    }

    public function postRemove(Channel $channel, LifecycleEventArgs $eventArgs)
    {
        $this->stateProvider->processChannelChange();
    }
}
