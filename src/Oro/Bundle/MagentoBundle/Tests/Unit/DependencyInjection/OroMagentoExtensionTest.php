<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\MagentoBundle\DependencyInjection\Configuration;
use Oro\Bundle\MagentoBundle\DependencyInjection\OroMagentoExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroMagentoExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testConfigPassedToConnectors()
    {
        $config = [
            'sync_settings' => [
                'mistiming_assumption_interval' => '10 minutes',
                'import_step_interval' => '1 day',
                'region_sync_interval' => '1 day',
                'skip_ssl_verification' => false
            ]
        ];

        $container = new ContainerBuilder();
        $container->getParameterBag()->set('kernel.environment', 'prod');
        $extension = new OroMagentoExtension();

        $extension->load(['oro_magento' => $config], $container);

        $tagged = $container->findTaggedServiceIds('oro_magento.bundle_config.aware');

        $missedConfigDefinitions = [];
        foreach (array_keys($tagged) as $serviceId) {
            $definition = $container->getDefinition($serviceId);

            $definition->getArguments();
            $configArguments = array_filter(
                $definition->getArguments(),
                function ($arg) use ($config) {
                    return $arg === $config;
                }
            );

            if (!$configArguments) {
                $missedConfigDefinitions[] = $serviceId;
            }
        }

        $this->assertEquals([], $missedConfigDefinitions, 'Should contain config array');
    }

    public function testInvalidAccountDiscoveryConfiguration()
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('Strategy configuration contains unknown fields "unknown_field"');

        $config = [
            [
                'account_discovery' => [
                    'fields'   => [
                        'field1' => null,
                        'field2' => [
                            'field2.1' => null
                        ]
                    ],
                    'strategy' => [
                        'field1'        => 'some',
                        'unknown_field' => 'other'
                    ]
                ]
            ],
            null
        ];
        $container = new ContainerBuilder();
        $container->getParameterBag()->set('kernel.environment', 'prod');
        $extension = new OroMagentoExtension();
        $extension->load($config, $container);
    }

    /**
     * @dataProvider inheritanceConfigurationDataProvider
     *
     * @param array $config
     * @param array $resultConfig
     */
    public function testInheritanceConfiguration(array $config, array $resultConfig)
    {
        $container = new ContainerBuilder();
        $container->getParameterBag()->set('kernel.environment', 'prod');
        $extension = new OroMagentoExtension();
        $extension->load($config, $container);
        $services = $container->findTaggedServiceIds('oro_magento.bundle_config.aware');
        foreach ($services as $serviceId => $tagAttributes) {
            $tagAttributes = reset($tagAttributes);
            if (isset($tagAttributes['argument_number'])) {
                $serviceDefinition = $container->getDefinition($serviceId);
                $serviceArgument   = $serviceDefinition->getArgument($tagAttributes['argument_number']);
                self::assertArrayHasKey(Configuration::DISCOVERY_NODE, $serviceArgument);
                self::assertArrayHasKey(
                    Configuration::DISCOVERY_OPTIONS_KEY,
                    $serviceArgument[Configuration::DISCOVERY_NODE]
                );
                self::assertArrayHasKey(
                    Configuration::DISCOVERY_STRATEGY_KEY,
                    $serviceArgument[Configuration::DISCOVERY_NODE]
                );
                self::assertArrayHasKey(
                    Configuration::DISCOVERY_FIELDS_KEY,
                    $serviceArgument[Configuration::DISCOVERY_NODE]
                );
                self::assertEquals(
                    $serviceArgument[Configuration::DISCOVERY_NODE],
                    $resultConfig[Configuration::DISCOVERY_NODE]
                );
            }
        }
    }

    /**
     * @return array
     */
    public function inheritanceConfigurationDataProvider()
    {
        return [
            'one config block' => [
                'config'       => [
                    [
                        'account_discovery' => [
                            'fields'  => ['field1' => null, 'field2' => null, 'field3' => null,],
                            'options' => ['match' => 'first', 'empty' => false,]
                        ]
                    ],
                    null,
                ],
                'resultConfig' => [
                    'account_discovery' => [
                        'fields'   => ['field1' => null, 'field2' => null, 'field3' => null,],
                        'options'  => ['match' => 'first', 'empty' => false,],
                        'strategy' => []
                    ]
                ]
            ],
            'two config block' => [
                'config'       => [
                    [
                        'account_discovery' => [
                            'fields'  => ['field1' => null, 'field2' => null, 'field3' => null],
                            'options' => ['match' => 'first', 'empty' => false]
                        ]
                    ],
                    [
                        'account_discovery' => [
                            'fields'  => ['field1' => null],
                            'options' => ['match' => 'first', 'empty' => false]
                        ]
                    ],
                    null,
                ],
                'resultConfig' => [
                    'account_discovery' => [
                        'fields'   => ['field1' => null,],
                        'options'  => ['match' => 'first', 'empty' => false,],
                        'strategy' => []
                    ]
                ]
            ],
        ];
    }
}
