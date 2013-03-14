<?php

namespace Oro\Bundle\GridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AddFilterTypeCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->injectEntityTypesByTag($container, 'oro_grid.filter.factory', 'oro_grid.filter.type');
        $this->injectEntityTypesByTag($container, 'oro_grid.action.factory', 'oro_grid.action.type');
    }

    /**
     * @param ContainerBuilder $container
     * @param string $definitionId
     * @param string $tagName
     */
    protected function injectEntityTypesByTag(ContainerBuilder $container, $definitionId, $tagName)
    {
        $definition = $container->getDefinition($definitionId);
        $types      = array();

        foreach ($container->findTaggedServiceIds($tagName) as $id => $attributes) {
            $container->getDefinition($id)->setScope(ContainerInterface::SCOPE_PROTOTYPE);

            foreach ($attributes as $eachTag) {
                $types[$eachTag['alias']] = $id;
            }
        }

        $definition->replaceArgument(1, $types);
    }
}
