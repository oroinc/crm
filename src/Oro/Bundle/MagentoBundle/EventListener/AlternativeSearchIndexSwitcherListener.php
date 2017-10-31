<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\IntegrationBundle\Event\SyncEvent;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;

/**
 * Class AlternativeSearchIndexSwitcherListener specially created to enable/disable
 * oro_magento.event_listener.delayed_search_reindex listener only for Magento integration
 */
class AlternativeSearchIndexSwitcherListener
{
    /** @var  OptionalListenerManager */
    protected $listenerManager;

    /**
     * AlternativeSearchIndexSwitcherListener constructor.
     *
     * @param OptionalListenerManager $listenerManager
     */
    public function __construct(OptionalListenerManager $listenerManager)
    {
        $this->listenerManager = $listenerManager;
    }

    /**
     * Enables special search index listener during Magento integration
     *
     * @param SyncEvent $event
     */
    public function onStart(SyncEvent $event)
    {
        if ($this->isMagentoIntegration($event)) {
            $this->listenerManager->disableListener('oro_search.index_listener');
            $this->listenerManager->enableListener('oro_magento.event_listener.delayed_search_reindex');
        }
    }

    /**
     * Returns standard search listener
     *
     * @param SyncEvent $event
     */
    public function onFinish(SyncEvent $event)
    {
        if ($this->isMagentoIntegration($event)) {
            $this->listenerManager->disableListener('oro_magento.event_listener.delayed_search_reindex');
            $this->listenerManager->enableListener('oro_search.index_listener');
        }
    }

    /**
     * Checks if it's Magento integration
     *
     * @param SyncEvent $event
     *
     * @return bool return true for Magento integration
     */
    protected function isMagentoIntegration(SyncEvent $event)
    {
        return strpos($event->getJobName(), 'mage_') !== false;
    }
}
