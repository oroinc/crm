<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\DependencyInjection;

use Oro\Component\Config\CumulativeResourceManager;
use Oro\Bundle\ChannelBundle\DependencyInjection\CompilerPass\SettingsPass;
use Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Bundles\TestBundle1\TestBundle1;
use Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Bundles\TestBundle2\TestBundle2;

class ChannelConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $expected = [
            'entity_data'   => [
                [
                    'name'                   => 'Oro\Bundle\TestBundle1\Entity\Entity1',
                    'dependent'              => [
                        'Oro\Bundle\TestBundle1\Entity\Entity1Status',
                        'Oro\Bundle\TestBundle1\Entity\Entity1Reason'
                    ],
                    'dependencies'           => [
                        'Oro\Bundle\TestBundle1\Entity\Entity2',
                        'Oro\Bundle\TestBundle1\Entity\Entity3'
                    ],
                    'dependencies_condition' => 'OR',
                    'navigation_items'       => [
                        'application_menu.menu1.list',
                    ],
                    'belongs_to'             => [
                        'integration' => 'testIntegrationType'
                    ]
                ],
                [
                    'name'                   => 'Oro\Bundle\TestBundle2\Entity\Entity',
                    'dependent'              => [
                        'Oro\Bundle\TestBundle2\Entity\EntityContact'
                    ],
                    'navigation_items'       => [
                        'application_menu.activities_tab.contact',
                    ],
                    'dependencies'           => [],
                    'dependencies_condition' => 'AND',
                ],
            ],
            'channel_types' => [
                'test1' => [
                    'label'             => 'test1 type',
                    'entities'          => [
                        'Oro\Bundle\TestBundle1\Entity\Entity1',
                        'Oro\Bundle\TestBundle1\Entity\Entity2',
                        'Oro\Bundle\TestBundle1\Entity\Entity3',
                    ],
                    'integration_type'  => 'test',
                    'customer_identity' => 'Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer',
                    'lifetime_value'    => 'some_field',
                    'priority'          => 0,
                    'system'            => false
                ],
                'test2' => [
                    'label'             => 'test2 type',
                    'entities'          => [],
                    'customer_identity' => 'Oro\Bundle\ChannelBundle\Entity\CustomerIdentity',
                    'priority'          => 0,
                    'system'            => false
                ]
            ],
        ];

        $bundle1 = new TestBundle1();
        $bundle2 = new TestBundle2();

        CumulativeResourceManager::getInstance()
            ->clear()
            ->setBundles([$bundle1->getName() => get_class($bundle1), $bundle2->getName() => get_class($bundle2)]);
        $settingsProviderDef = $this->getMockBuilder('Symfony\Component\DependencyInjection\Definition')
            ->disableOriginalConstructor()
            ->getMock();

        $result    = null;
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->once())
            ->method('getDefinition')
            ->with(SettingsPass::SETTINGS_PROVIDER_ID)
            ->will($this->returnValue($settingsProviderDef));

        $settingsProviderDef->expects($this->once())
            ->method('replaceArgument')
            ->will(
                $this->returnCallback(
                    function ($index, $argument) use (&$result) {
                        $result = $argument;
                    }
                )
            );

        $compiler = new SettingsPass();
        $compiler->process($container);
        $this->assertEquals($expected, $result);
    }
}
