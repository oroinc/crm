<?php

namespace Oro\Bundle\NavigationBundle\Menu;

use Knp\Menu\ItemInterface;

class NavigationMostviewedBuilder extends NavigationItemBuilder
{
    /**
     * @var \Oro\Bundle\ConfigBundle\Config\UserConfigManager
     */
    private $configOptions = null;

    /**
     * Inject config
     *
     * @param $config
     */
    public function setOptions($config)
    {
        $this->configOptions = $config;
    }

    /**
     * Modify menu by adding, removing or editing items.
     *
     * @param \Knp\Menu\ItemInterface $menu
     * @param array $options
     * @param string|null $alias
     */
    public function build(ItemInterface $menu, array $options = array(), $alias = null)
    {
        $options['showMostviewed'] = true;
        $maxItems = $this->configOptions->get('oro_navigation.maxItems');
        if (!is_null($maxItems)) {
            $options['maxItems'] = $maxItems;
        }
        parent::build($menu, $options, $alias);
    }
}
