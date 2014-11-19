<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\DependencyInjection;

use OroCRM\Bundle\MagentoBundle\DependencyInjection\OroCRMMagentoExtension;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroCRMMagentoExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigPassedToConnectors()
    {
        $config = ['sync_settings' => ['mistiming_assumption_interval' => '10 minutes']];

        $container = new ContainerBuilder();
        $extension = new OroCRMMagentoExtension();

        $extension->load(['orocrm_magento' => $config], $container);

        $tagged = $container->findTaggedServiceIds('orocrm_magento.bundle_config.aware');

        foreach (array_keys($tagged) as $serviceId) {
            $definition = $container->getDefinition($serviceId);

            $configArguments = array_filter(
                $definition->getArguments(),
                function ($arg) use ($config) {
                    return $arg === $config;
                }
            );

            $this->assertNotEmpty($configArguments, "$serviceId should contain config array");
        }
    }
}
