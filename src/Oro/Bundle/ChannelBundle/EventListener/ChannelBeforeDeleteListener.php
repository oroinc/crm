<?php

namespace Oro\Bundle\ChannelBundle\EventListener;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelBeforeDeleteEvent;
use Oro\Bundle\IntegrationBundle\Manager\DeleteManager;

class ChannelBeforeDeleteListener
{
    /** @var DeleteManager  */
    protected $manager;

    public function __construct(DeleteManager $manager)
    {
        $this->manager = $manager;
    }

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
