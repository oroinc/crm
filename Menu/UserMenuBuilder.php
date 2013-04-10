<?php

namespace Oro\Bundle\UserBundle\Menu;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Menu\BuilderInterface;

class UserMenuBuilder implements BuilderInterface
{
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function build(ItemInterface $menu, array $options = array(), $alias = null)
    {
        $menu->setExtra('type', 'dropdown');
        $menu->addChild(
            'My profile',
            array(
                 'route'           => 'oro_user_show',
                 'routeParameters' => array('id' => $this->securityContext->getToken()->getUser()->getId())
            )
        );
        $menu->addChild(
            'Update status',
            array(
                 'route'      => 'oro_user_status_create',
                 'attributes' => array(
                     'class' => 'update-status'
                 )
            )
        );

        $menu->addChild('divider-' . rand(1, 99999))
            ->setLabel('')
            ->setAttribute('class', 'divider');
        $menu->addChild('Logout', array('route' => 'oro_user_security_logout'));
    }
}
