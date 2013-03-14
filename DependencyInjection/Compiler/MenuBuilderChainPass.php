<?php
namespace Oro\Bundle\NavigationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Config\Definition\Processor;

class MenuBuilderChainPass implements CompilerPassInterface
{
    const BUILDER_TAG = 'oro_menu.builder';
    const PROVIDER_KEY = 'oro_menu.builder_chain';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::PROVIDER_KEY)) {
            return;
        }

        $definition = $container->getDefinition(self::PROVIDER_KEY);
        $taggedServices = $container->findTaggedServiceIds(self::BUILDER_TAG);

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $addBuilderArgs = array(new Reference($id));

                if (!empty($attributes['alias'])) {
                    $addBuilderArgs[] = $attributes['alias'];
                }

                $definition->addMethodCall('addBuilder', $addBuilderArgs);
            }
        }
    }
}
