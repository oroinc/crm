<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use Oro\Bundle\ChannelBundle\EventListener\NavigationListener;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTrait;

class NavigationListenerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var SettingsProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $settings;

    /** @var StateProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $state;

    /** @var NavigationListener */
    private $listener;

    protected function setUp(): void
    {
        $this->settings = $this->createMock(SettingsProvider::class);
        $this->state = $this->createMock(StateProvider::class);

        $this->listener = new NavigationListener($this->settings, $this->state);
    }

    /**
     * @dataProvider navigationConfigureDataProvider
     */
    public function testOnNavigationConfigure(array $entities, bool $isEnabled, bool $expectedResult)
    {
        $factory = new MenuFactory();

        $this->settings->expects(self::once())
            ->method('getEntities')
            ->willReturn($entities);
        $this->state->expects(self::once())
            ->method('isEntityEnabled')
            ->willReturn($isEnabled);

        $menu = new MenuItem('test_menu', $factory);
        $salesTab = new MenuItem('sales_tab', $factory);

        $salesTab->addChild('test_item')->setDisplay(false);
        $menu->addChild($salesTab);

        self::assertFalse($salesTab->getChild('test_item')->isDisplayed());

        $eventData = new ConfigureMenuEvent($factory, $menu);
        $this->listener->onNavigationConfigure($eventData);

        self::assertEquals(
            $expectedResult,
            $salesTab->getChild('test_item')->isDisplayed()
        );
    }

    public function navigationConfigureDataProvider(): array
    {
        return [
            'child is shown'                               => [
                [
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
                    ]
                ],
                true,
                true
            ],
            'child is hidden'                              => [
                [
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
                    ]
                ],
                false,
                false
            ],
            'another menu is configured, should skip item' => [
                [
                    [
                        'name'                   => 'Oro\Bundle\AcmeBundle\Entity\Test',
                        'dependent'              => [],
                        'navigation_items'       => [
                            'test_menu_another.sales_tab.test_item'
                        ],
                        'dependencies'           => [],
                        'dependencies_condition' => 'AND'
                    ]
                ],
                true,
                false
            ]
        ];
    }

    public function testOnChannelSave()
    {
        /** @var Organization $org */
        $org =  $this->getEntity(Organization::class, ['id' => 2]);
        $channel = new Channel();
        $channel->setOwner($org);
        $event = new ChannelSaveEvent($channel);

        $this->state->expects($this->once())
            ->method('clearOrganizationCache')
            ->with(2);

        $this->listener->onChannelSave($event);
    }
}
