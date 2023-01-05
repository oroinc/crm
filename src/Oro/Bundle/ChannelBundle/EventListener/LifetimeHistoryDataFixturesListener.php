<?php

namespace Oro\Bundle\ChannelBundle\EventListener;

use Oro\Bundle\ChannelBundle\Entity\Manager\LifetimeHistoryStatusUpdateManager;
use Oro\Bundle\MigrationBundle\Event\MigrationDataFixturesEvent;

/**
 * Disables queue usage by LifetimeHistoryStatusUpdateManager during LoadDataFixturesCommand command execution
 */
class LifetimeHistoryDataFixturesListener
{
    private LifetimeHistoryStatusUpdateManager $statusUpdateManager;

    public function __construct(LifetimeHistoryStatusUpdateManager $statusUpdateManager)
    {
        $this->statusUpdateManager = $statusUpdateManager;
    }

    public function onPreLoad(MigrationDataFixturesEvent $event): void
    {
        $this->statusUpdateManager->setUseQueue(false);
    }
}
