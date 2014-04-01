<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\EntityManager;

use Knp\Menu\ItemInterface;

use Symfony\Component\Routing\RouterInterface;

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
            'parent'         => 'sales_tab',
            'prefix'         => self::CART_MENU_ITEM,
            'label'          => 'Shopping Carts',
            'route'          => 'orocrm_magento_cart_index',
            'extra_routes'   => '/^orocrm_magento_cart_(index|view)$/',
            'extra_position' => 40
        ],
        'order'    => [
            'parent'         => 'sales_tab',
            'prefix'         => self::ORDER_MENU_ITEM,
            'label'          => 'Orders',
            'route'          => 'orocrm_magento_order_index',
            'extra_routes'   => '/^orocrm_magento_order_(index|view)$/',
            'extra_position' => 50
        ],
        'customer' => [
            'parent'       => 'customers_tab',
            'prefix'       => self::CUSTOMER_MENU_ITEM,
            'label'        => 'orocrm.magento.menu.web_customers',
            'route'        => 'orocrm_magento_customer_index',
            'extra_routes' => '/^orocrm_magento_customer_(index|view)$/'
        ]
    ];

    /** @var EntityManager */
    protected $em;

    /** @var RouterInterface */
    protected $router;

    public function __construct(EntityManager $em, RouterInterface $router)
    {
        $this->em     = $em;
        $this->router = $router;
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
                            $entries[$connector] = [];
                        }
                        $entries[$connector][] = ['id' => $channel->getId(), 'label' => $channel->getName()];
                    }
                }
            }

            // walk trough prepared array
            foreach ($entries as $key => $items) {
                if (isset(self::$map[$key])) {
                    /** @var ItemInterface $reportsMenuItem */
                    $salesMenuItem = $event->getMenu()->getChild(self::$map[$key]['parent']);
                    $child         = $salesMenuItem
                        ->addChild(self::$map[$key]['prefix'], ['label' => self::$map[$key]['label'], 'uri' => '#']);
                    foreach ($items as $entry) {
                        $child->addChild(
                            implode([self::$map[$key]['prefix'], $entry['id']]),
                            [
                                'route'           => self::$map[$key]['route'],
                                'routeParameters' => ['id' => $entry['id']],
                                'label'           => $entry['label'],
                                'extras'          => ['routes' => self::$map[$key]['extra_routes']]
                            ]
                        );
                    }
                }
            }
        }
    }
}
