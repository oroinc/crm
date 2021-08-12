<?php

namespace Oro\Bundle\ChannelBundle\EventListener;

use Oro\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;

/**
 * Hide menu items that were not enabled it config
 */
class NavigationListener
{
    /** @var SettingsProvider */
    private $settings;

    /** @var StateProvider */
    private $state;

    public function __construct(SettingsProvider $settings, StateProvider $state)
    {
        $this->settings = $settings;
        $this->state = $state;
    }

    public function onNavigationConfigure(ConfigureMenuEvent $event)
    {
        foreach ($this->settings->getEntities() as $setting) {
            if (!$this->state->isEntityEnabled($setting['name'])) {
                continue;
            }

            foreach ($setting['navigation_items'] as $item) {
                $navigateArray = explode('.', $item);
                $menu = $event->getMenu();

                if ($menu->getName() !== $navigateArray[0]) {
                    continue;
                }

                $navigateArrayCount = count($navigateArray);
                for ($i = 1; $i < $navigateArrayCount; $i++) {
                    if ($menu->getChild($navigateArray[$i])) {
                        /** redefinition of variable $menu */
                        $menu = $menu->getChild($navigateArray[$i]);
                    }
                    if ($menu && !$menu->isDisplayed()) {
                        $menu->setDisplay(true);
                    }
                }
            }
        }
    }

    public function onChannelSave(ChannelSaveEvent $event)
    {
        if ($event->getChannel() && $event->getChannel()->getOwner()) {
            $this->state->clearOrganizationCache($event->getChannel()->getOwner()->getId());
        }
    }
}
