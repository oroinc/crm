<?php

namespace Oro\Bundle\NavigationBundle\Menu;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;;
use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Matcher;

class NavigationHistoryBuilder extends NavigationItemBuilder
{
    /**
     * Modify menu by adding, removing or editing items.
     *
     * @param \Knp\Menu\ItemInterface $menu
     * @param array $options
     * @param string|null $alias
     */
    public function build(ItemInterface $menu, array $options = array(), $alias = null)
    {
        parent::build($menu, $options, $alias);

        $children = $menu->getChildren();
        /** @var $matcher Matcher */
        $matcher = $this->container->get('knp_menu.matcher');

        foreach ($children as $child) {
            if ($matcher->isCurrent($child)) {
                $menu->removeChild($child);

                break;
            }
        }

        $menu->slice(0, 20);
    }
}
