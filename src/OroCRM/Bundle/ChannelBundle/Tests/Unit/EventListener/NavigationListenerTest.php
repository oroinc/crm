<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;

use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;

use Oro\Component\Config\Resolver\ResolverInterface;

use OroCRM\Bundle\ChannelBundle\EventListener\NavigationListener;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;
use OroCRM\Bundle\ChannelBundle\Provider\StateProvider;

class NavigationListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ResolverInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $resolver;

    /** @var StateProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $state;

    protected function setUp()
    {
        $this->resolver = $this->getMockBuilder('Oro\Component\Config\Resolver\ResolverInterface')
            ->disableOriginalConstructor()->getMock();
        $this->state    = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\StateProvider')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @dataProvider navigationConfigureDataProvider
     *
     * @param array   $settings
     * @param boolean $isDisplayed default is true
     */
    public function testOnNavigationConfigure($settings, $isDisplayed = true)
    {
        $factory = new MenuFactory();

        $this->resolver->expects($this->any())->method('resolve')
            ->will($this->returnArgument(0));

        $this->state->expects($this->once())->method('isEntityEnabled')
            ->will($this->returnValue($isDisplayed));

        $settingsProvider = new SettingsProvider($settings, $this->resolver);
        $listener         = new NavigationListener($settingsProvider, $this->state);
        $menu             = new MenuItem('test_menu', $factory);
        $salesTab         = new MenuItem('sales_tab', $factory);

        $salesTab->addChild('test_item')->setDisplay(false);
        $menu->addChild($salesTab);

        $this->assertFalse($salesTab->getChild('test_item')->isDisplayed());

        $eventData = new ConfigureMenuEvent($factory, $menu);
        $listener->onNavigationConfigure($eventData);

        $this->assertEquals(
            $isDisplayed,
            $salesTab->getChild('test_item')->isDisplayed()
        );
    }

    /**
     * @return array
     */
    public function navigationConfigureDataProvider()
    {
        return [
            'child is shown'  => [
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
            ],
            'child is hidden' => [
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
                false
            ],
        ];
    }
}
