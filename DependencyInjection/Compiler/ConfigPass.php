<?php

namespace Oro\Bundle\ConfigBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Config\Definition\Processor;

class ConfigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $processor = new Processor();
        $settings  = array();

        foreach ($container->getExtensions() as $name => $extension) {
            if (strpos($name, 'oro_') !== false) {
                $config = $processor->processConfiguration(
                    $extension->getConfiguration(array(), $container),
                    $container->getExtensionConfig($name)
                );

                if (isset($config['settings'])) {
                    $settings[$name] = $config['settings'];
                }
            }
        }

        $container
            ->getDefinition('oro_config')
            ->addArgument($settings);
    }
}
