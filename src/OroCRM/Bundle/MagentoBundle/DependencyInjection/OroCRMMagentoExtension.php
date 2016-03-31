<?php

namespace OroCRM\Bundle\MagentoBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class OroCRMMagentoExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('orm.yml');
        $loader->load('importexport.yml');

        $config  = $this->processConfiguration(new Configuration(), $configs);
        $services = $container->findTaggedServiceIds('orocrm_magento.bundle_config.aware');

        foreach ($services as $serviceId => $tagAttributes) {
            $tagAttributes = reset($tagAttributes);
            if (isset($tagAttributes['argument_number'])) {
                $container->getDefinition($serviceId)->replaceArgument($tagAttributes['argument_number'], $config);
            } else {
                $container->getDefinition($serviceId)->addArgument($config);
            }
        }
    }
}
