<?php

namespace Oro\Bundle\UserBundle\Menu;

use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;

class UserMenuBuilder implements BuilderInterface
{
    public function build(ItemInterface $menu, array $options = array(), $alias = null)
    {
        $menu->addChild('Update status', array(
            'route' => 'oro_user_status_create',
            'attributes' => array(
                'class' => 'update-status'
            )
        ));
        $menu->addChild('divider-' . rand(1, 99999))
            ->setLabel('')
            ->setAttribute('class', 'divider');
        $menu->setExtra('type', 'dropdown');
        $menu->addChild('Account', array('uri' => '#'));
        $menu->addChild('Admin', array('uri' => '#'));
        $menu->addChild('Settings', array('uri' => '#'));
        $menu->addChild('divider-' . rand(1, 99999))
            ->setLabel('')
            ->setAttribute('class', 'divider');
        $menu->addChild('Status updates', array('route' => 'oro_user_status_list'));
        $menu->addChild('Logout', array('route' => 'oro_user_security_logout'));
    }
}
