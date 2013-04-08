<?php

namespace Oro\Bundle\NavigationBundle\Menu;

use Knp\Menu\ItemInterface;

class NavigationMostviewedBuilder extends NavigationItemBuilder
{
    const DEFAULT_MAX_RESULTS = 20;

    /**
     * Modify menu by adding, removing or editing items.
     *
     * @param \Knp\Menu\ItemInterface $menu
     * @param array $options
     * @param string|null $alias
     */
    public function build(ItemInterface $menu, array $options = array(), $alias = null)
    {
        $options['sortBy'] = 'visitCount';
        if (!isset($options['maxItems'])) {
            $options['maxItems'] = self::DEFAULT_MAX_RESULTS;
        }
        parent::build($menu, $options, $alias);
    }
}
