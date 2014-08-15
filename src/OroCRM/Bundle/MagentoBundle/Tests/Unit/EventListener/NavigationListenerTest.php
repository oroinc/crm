<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;



use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use OroCRM\Bundle\MagentoBundle\EventListener\NavigationListener;

class NavigationListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NavigationListener
     */
    protected $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    protected function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->listener = new NavigationListener($this->em);
    }

    /**
     * @dataProvider navigationConfigureDataProvider
     */
    public function testOnNavigationConfigure($channels, $menuChild = '', $menuItem = '')
    {
        $factory = new MenuFactory();

        $menu = new MenuItem('test', $factory);
        $salesTab = new MenuItem('sales_tab', $factory);
        $customersTab = new MenuItem('customers_tab', $factory);
        $menu->addChild($salesTab);
        $menu->addChild($customersTab);

        $eventData = new ConfigureMenuEvent($factory, $menu);

        $repo = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('getConfiguredChannelsForSync')
            ->will($this->returnValue($channels));
        $this->em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repo));

        $this->listener->onNavigationConfigure($eventData);

        if (!$menuChild) {
            $resultTab = $menu->getChild($menuChild);
            $this->assertTrue($resultTab->hasChildren($menuItem));
        }
    }

    public function navigationConfigureDataProvider()
    {
        $cartChannel = new Channel();
        $cartChannel->setName('cart_test');
        $cartChannel->setConnectors(['cart']);

        $orderChannel = new Channel();
        $orderChannel->setName('order_test');
        $orderChannel->setConnectors(['order']);

        $customerChannel = new Channel();
        $customerChannel->setName('customer_test');
        $customerChannel->setConnectors(['customer']);

        return [
            [
                [
                    $cartChannel
                ],
                'sales_tab',
                'magento_cart'
            ],
            [
                [
                    $orderChannel
                ],
                'sales_tab',
                'magento_order'
            ],
            [
                [
                    $customerChannel
                ],
                'customers_tab',
                'magento_customer'
            ],
        ];
    }
}
