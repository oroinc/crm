<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\DependencyInjection;

use OroCRM\Bundle\MagentoBundle\DependencyInjection\OroCRMMagentoExtension;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroCRMMagentoExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigPassedToConnectors()
    {
        $config = [
            'sync_settings' => [
                'mistiming_assumption_interval' => '10 minutes',
                'initial_import_step_interval' => '1 day',
                'region_sync_interval' => '1 day',
                'skip_ssl_verification' => false
            ]
        ];

        $container = new ContainerBuilder();
        $extension = new OroCRMMagentoExtension();

        $extension->load(['oro_crm_magento' => $config], $container);

        $tagged = $container->findTaggedServiceIds('orocrm_magento.bundle_config.aware');

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

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Strategy configuration contains unknown fields "unknown_field"
     */
    public function testInvalidAccountDiscoveryConfiguration()
    {
        $config = [
            'account_discovery' => [
                'fields' => [
                    'field1' => null,
                    'field2' => [
                        'field2.1' => null
                    ]
                ],
                'strategy' => [
                    'field1' => 'some',
                    'unknown_field' => 'other'
                ]
            ]
        ];

        $container = new ContainerBuilder();
        $extension = new OroCRMMagentoExtension();

        $extension->load(['oro_crm_magento' => $config], $container);
    }
}
