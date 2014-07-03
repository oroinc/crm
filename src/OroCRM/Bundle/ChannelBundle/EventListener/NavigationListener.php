<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;

use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;
use OroCRM\Bundle\ChannelBundle\Provider\StateProvider;

/**
 * Hide menu items that were not enabled it config
 */
class NavigationListener
{
    /** @var SettingsProvider */
    protected $settings;

    /** @var StateProvider  */
    protected $state;

    /**
     * @param SettingsProvider $settings
     * @param StateProvider    $state
     */
    public function __construct(SettingsProvider $settings, StateProvider $state)
    {
        $this->settings = $settings;
        $this->state    = $state;
    }

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onNavigationConfigure(ConfigureMenuEvent $event)
    {
        foreach ($this->getSettings() as $setting) {
            if (!$this->state->isEntityEnabled($setting['name'])) {
                continue;
            }

            foreach ($setting['navigation_items'] as $item) {
                $navigateArray = explode('.', $item);
                $menu          = $event->getMenu();

                if ($menu->getName() !== $navigateArray[0]) {
                    continue;
                }

                for ($i = 1; $i < count($navigateArray); $i++) {
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

    /**
     * @return array
     */
    protected function getSettings()
    {
        $settings = $this->settings->getSettings();
        return !empty($settings['entity_data']) ? $settings['entity_data'] : [];
    }
}
