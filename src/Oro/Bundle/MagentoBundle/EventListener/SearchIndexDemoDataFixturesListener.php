<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\MigrationBundle\Event\MigrationDataFixturesEvent;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;

/**
 * Disables search index listener during loading of demo data,
 * because full reindexation is performed after demo data are loaded.
 */
class SearchIndexDemoDataFixturesListener
{
    /**
     * This listener is disabled to prevent a lot of reindex messages
     */
    const SEARCH_INDEX_LISTENER = 'oro_magento.event_listener.delayed_search_reindex';

    /** @var OptionalListenerManager */
    private $listenerManager;

    /**
     * @param OptionalListenerManager $listenerManager
     */
    public function __construct(OptionalListenerManager $listenerManager)
    {
        $this->listenerManager = $listenerManager;
    }

    /**
     * @param MigrationDataFixturesEvent $event
     */
    public function onPreLoad(MigrationDataFixturesEvent $event)
    {
        if ($event->isDemoFixtures()) {
            $this->listenerManager->disableListener(self::SEARCH_INDEX_LISTENER);
        }
    }

    /**
     * @param MigrationDataFixturesEvent $event
     */
    public function onPostLoad(MigrationDataFixturesEvent $event)
    {
        if ($event->isDemoFixtures()) {
            $this->listenerManager->enableListener(self::SEARCH_INDEX_LISTENER);
        }
    }
}
