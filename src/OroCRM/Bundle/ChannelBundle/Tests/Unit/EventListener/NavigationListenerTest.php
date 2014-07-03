<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityRepository;

use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;

use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;

use Oro\Component\Config\Resolver\ResolverInterface;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\EventListener\NavigationListener;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;
use OroCRM\Bundle\ChannelBundle\Provider\StateProvider;

class NavigationListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var ResolverInterface */
    protected $resolver;

    /** @var EntityRepository */
    protected $repo;

    /** @var StateProvider */
    protected $state;

    protected function setUp()
    {
        $this->resolver = $this->getMockBuilder('Oro\Component\Config\Resolver\ResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em       = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->repo     = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->state     = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\StateProvider')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider navigationConfigureDataProvider
     */
    public function testOnNavigationConfigure($settings, $factory, $channel)
    {
        $this->resolver->expects($this->once())
            ->method('resolve')
            ->will($this->returnValue($settings));

        $this->em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($this->repo));

        $this->repo->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue([$channel]));

        $this->state->expects($this->at(0))
            ->method('isEntityEnabled')
            ->will($this->returnValue(true));

        $settingsProvider = new SettingsProvider($settings, $this->resolver);
        $listener         = new NavigationListener($this->em, $settingsProvider, $this->state);
        $menu             = new MenuItem('test_menu', $factory);
        $salesTab         = new MenuItem('sales_tab', $factory);

        $salesTab->addChild('test_item')->setDisplay(false);
        $menu->addChild($salesTab);

        $this->assertFalse($salesTab->getChild('test_item')->isDisplayed());

        $eventData = new ConfigureMenuEvent($factory, $menu);
        $listener->onNavigationConfigure($eventData);

        $this->assertTrue($salesTab->getChild('test_item')->isDisplayed());
    }

    public function navigationConfigureDataProvider()
    {
        $channel = new Channel();
        $channel->setName('test');
        $menuFactory = new MenuFactory();

        return [
            'navigation listener test' => [
                'settings' => [
                    'entity_data' => [
                        [
                            'name'                   => 'Oro\Bundle\AcmeBundle\Entity\Test',
                            'dependent'              => [
                                'Oro\Bundle\AcmeBundle\EntityTestAddress',
                                'Oro\Bundle\AcmeBundle\Entity\TestItem'
                            ],
                            'navigation_items'       => [
                                'test_menu.sales_tab.test_item'
                            ],
                            'dependencies'           => [],
                            'dependencies_condition' => 'AND'
                        ],
                    ]
                ],
                $menuFactory,
                $channel,
            ],
        ];
    }
}
