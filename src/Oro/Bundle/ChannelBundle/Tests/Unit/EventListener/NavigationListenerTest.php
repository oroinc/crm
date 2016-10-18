<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Knp\Menu\MenuItem;
use Knp\Menu\MenuFactory;

use Oro\Component\Config\Resolver\ResolverInterface;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\ChannelBundle\EventListener\NavigationListener;

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
        $this->state    = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Provider\StateProvider')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * @dataProvider navigationConfigureDataProvider
     *
     * @param array $settings
     * @param bool  $isEnabled
     * @param bool  $expectedResult
     */
    public function testOnNavigationConfigure($settings, $isEnabled, $expectedResult)
    {
        $factory = new MenuFactory();

        $this->resolver->expects($this->any())->method('resolve')
            ->will($this->returnArgument(0));

        $this->state->expects($this->once())->method('isEntityEnabled')
            ->will($this->returnValue($isEnabled));

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
            $expectedResult,
            $salesTab->getChild('test_item')->isDisplayed()
        );
    }

    /**
     * @return array
     */
    public function navigationConfigureDataProvider()
    {
        return [
            'child is shown'                               => [
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
                true,
                true
            ],
            'child is hidden'                              => [
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
                false,
                false
            ],
            'another menu is configured, should skip item' => [
                'settings' => [
                    'entity_data' => [
                        [
                            'name'                   => 'Oro\Bundle\AcmeBundle\Entity\Test',
                            'dependent'              => [],
                            'navigation_items'       => [
                                'test_menu_another.sales_tab.test_item'
                            ],
                            'dependencies'           => [],
                            'dependencies_condition' => 'AND'
                        ],
                    ]
                ],
                true,
                false
            ],
        ];
    }
}
