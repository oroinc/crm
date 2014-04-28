<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\EntityManager;

use Knp\Menu\ItemInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;

class NavigationListener
{
    const CART_MENU_ITEM     = 'magento_cart';
    const ORDER_MENU_ITEM    = 'magento_order';
    const CUSTOMER_MENU_ITEM = 'magento_customer';

    protected static $map = [
        'cart'     => [
            'parent' => 'sales_tab',
            'prefix' => self::CART_MENU_ITEM,
            'label'  => 'Shopping Carts',
            'route'  => 'orocrm_magento_cart_index',
            'extras' => [
                'routes'   => '/^orocrm_magento_cart_(index|view)|orocrm_magento_orderplace_cart$/',
                'position' => 40
            ]
        ],
        'order'    => [
            'parent' => 'sales_tab',
            'prefix' => self::ORDER_MENU_ITEM,
            'label'  => 'Orders',
            'route'  => 'orocrm_magento_order_index',
            'extras' => [
                'routes'   => '/^orocrm_magento_order_(index|view)$/',
                'position' => 50
            ]
        ],
        'customer' => [
            'parent' => 'customers_tab',
            'prefix' => self::CUSTOMER_MENU_ITEM,
            'label'  => 'orocrm.magento.menu.web_customers',
            'route'  => 'orocrm_magento_customer_index',
            'extras' => [
                'routes' => '/^orocrm_magento_customer_(index|view)$/',
            ]
        ]
    ];

    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Adds dynamically menu entries depends on configured channels
     *
     * @param ConfigureMenuEvent $event
     */
    public function onNavigationConfigure(ConfigureMenuEvent $event)
    {
        $repository = $this->em->getRepository('OroIntegrationBundle:Channel');
        $channels   = $repository->getConfiguredChannelsForSync(ChannelType::TYPE);

        if ($channels) {
            $entries = [];
            /** @var Channel $channel */
            foreach ($channels as $channel) {
                if ($channel->getConnectors()) {
                    foreach ($channel->getConnectors() as $connector) {
                        if (!isset($entries[$connector])) {
                            $entries[$connector] = true;
                        }
                    }
                }
            }

            // walk trough prepared array
            foreach (array_keys($entries) as $key) {
                if (isset(self::$map[$key])) {
                    /** @var ItemInterface $reportsMenuItem */
                    $salesMenuItem = $event->getMenu()->getChild(self::$map[$key]['parent']);
                    $salesMenuItem->addChild(
                        self::$map[$key]['prefix'],
                        [
                            'label'  => self::$map[$key]['label'],
                            'route'  => self::$map[$key]['route'],
                            'extras' => array_merge(self::$map[$key]['extras'], ['skipBreadcrumbs' => true])
                        ]
                    );
                }
            }
        }
    }
}
