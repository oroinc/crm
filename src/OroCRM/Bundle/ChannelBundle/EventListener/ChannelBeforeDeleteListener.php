<?php

namespace Oro\Bundle\ChannelBundle\EventListener;

use Oro\Bundle\IntegrationBundle\Manager\DeleteManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelBeforeDeleteEvent;

class ChannelBeforeDeleteListener
{
    /** @var DeleteManager  */
    protected $manager;

    /**
     * @param DeleteManager $manager
     */
    public function __construct(DeleteManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param ChannelBeforeDeleteEvent $event
     */
    public function onChannelBeforeDelete(ChannelBeforeDeleteEvent $event)
    {
        /** @var Channel $channel */
        $channel    = $event->getChannel();
        $dataSource = $channel->getDataSource();

        if ($dataSource) {
            $this->manager->delete($dataSource);
        }
    }
}
