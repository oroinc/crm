<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\DependencyInjection;

use Oro\Component\Config\CumulativeResourceManager;

use OroCRM\Bundle\ChannelBundle\DependencyInjection\CompilerPass\SettingsPass;
use OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Bundles\TestBundle1\TestBundle1;
use OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Bundles\TestBundle2\TestBundle2;

class ChannelConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $expected = [
            'entity_data'   => [
                [
                    'name'                   => 'OroCRM\Bundle\TestBundle1\Entity\Entity1',
                    'dependent'              => [
                        'OroCRM\Bundle\TestBundle1\Entity\Entity1Status',
                        'OroCRM\Bundle\TestBundle1\Entity\Entity1Reason'
                    ],
                    'dependencies'           => [
                        'OroCRM\Bundle\TestBundle1\Entity\Entity2',
                        'OroCRM\Bundle\TestBundle1\Entity\Entity3'
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
                    'name'                   => 'OroCRM\Bundle\TestBundle2\Entity\Entity',
                    'dependent'              => [
                        'OroCRM\Bundle\TestBundle2\Entity\EntityContact'
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
                        'OroCRM\Bundle\TestBundle1\Entity\Entity1',
                        'OroCRM\Bundle\TestBundle1\Entity\Entity2',
                        'OroCRM\Bundle\TestBundle1\Entity\Entity3',
                    ],
                    'integration_type'  => 'test',
                    'customer_identity' => 'OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer',
                    'lifetime_value'    => 'some_field',
                    'priority'          => 0
                ],
                'test2' => [
                    'label'             => 'test2 type',
                    'entities'          => [],
                    'customer_identity' => 'OroCRM\Bundle\ChannelBundle\Entity\CustomerIdentity',
                    'priority'          => 0
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
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
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
