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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NavigationListenerTest extends TestCase
{
    use EntityTrait;

    private SettingsProvider&MockObject $settings;
    private StateProvider&MockObject $state;
    private NavigationListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->settings = $this->createMock(SettingsProvider::class);
        $this->state = $this->createMock(StateProvider::class);

        $this->listener = new NavigationListener($this->settings, $this->state);
    }

    /**
     * @dataProvider navigationConfigureDataProvider
     */
    public function testOnNavigationConfigure(array $entities, bool $isEnabled, bool $expectedResult): void
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

    public function testOnChannelSave(): void
    {
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
