<?php

namespace Oro\Bundle\NavigationBundle\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Matcher;

class NavigationHistoryBuilder extends NavigationItemBuilder
{
    const DEFAULT_MAX_RESULTS = 20;

    /**
     * @var Matcher
     */
    private $matcher;

    /**
     * @var Marcher
     */
    private $matcher;

    /**
     * Modify menu by adding, removing or editing items.
     *
     * @param \Knp\Menu\ItemInterface $menu
     * @param array $options
     * @param string|null $alias
     */
    public function build(ItemInterface $menu, array $options = array(), $alias = null)
    {
        $maxItems = isset($options['maxItems']) ? (int)$options['maxItems'] : self::DEFAULT_MAX_RESULTS;
        $options['maxItems'] = $maxItems + 1;

        parent::build($menu, $options, $alias);

        $children = $menu->getChildren();
        foreach ($children as $child) {
            if ($this->matcher->isCurrent($child)) {
                $menu->removeChild($child);

                break;
            }
        }

        $menu->slice(0, $maxItems);
    }

    /**
     * Setter for matcher service
     *
     * @param \Knp\Menu\Matcher\Matcher $matcher
     * @return $this
     */
    public function setMatcher(Matcher $matcher)
    {
        $this->matcher = $matcher;

        return $this;
    }

    /**
     * Setter for matcher service
     *
     * @param \Knp\Menu\Matcher\Matcher $matcher
     * @return $this
     */
    public function setMatcher(Matcher $matcher)
    {
        $this->matcher = $matcher;

        return $this;
    }
}
