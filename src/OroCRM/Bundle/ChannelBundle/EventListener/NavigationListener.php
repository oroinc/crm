<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class NavigationListener
{
    /** @var EntityManager */
    protected $em;

    /** @var SettingsProvider */
    protected $settings;

    /**
     * @param EntityManager    $em
     * @param SettingsProvider $settings
     */
    public function __construct(EntityManager $em, SettingsProvider $settings)
    {
        $this->em       = $em;
        $this->settings = $settings;
    }

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onNavigationConfigure(ConfigureMenuEvent $event)
    {
        $channels = $this->em->getRepository('OroCRMChannelBundle:Channel')->findAll();

        if (!empty($channels)) {

            foreach ($this->getSettings() as $setting) {
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
    }

    /**
     * @return array
     */
    protected function getSettings()
    {
        $settings = $this->settings->getChannelSettings();
        return !empty($settings['entity_data']) ? $settings['entity_data'] : [];
    }
}
