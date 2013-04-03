<?php

namespace Oro\Bundle\GridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AddFilterTypeCompilerPass implements CompilerPassInterface
{
    const DATAGRID_FILTER_TAG = 'oro_grid.filter.type';
    const DATAGRID_FILTER_FACTORY_SERVICE = 'oro_grid.filter.factory';
    const DATAGRID_ACTION_TAG = 'oro_grid.action.type';
    const DATAGRID_ACTION_FACTORY_SERVICE = 'oro_grid.action.factory';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->injectEntityTypesByTag($container, self::DATAGRID_FILTER_FACTORY_SERVICE, self::DATAGRID_FILTER_TAG);
        $this->injectEntityTypesByTag($container, self::DATAGRID_ACTION_FACTORY_SERVICE, self::DATAGRID_ACTION_TAG);
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
                $index = !empty($eachTag['alias']) ? $eachTag['alias'] : $id;
                $types[$index] = $id;
            }
        }

        $definition->replaceArgument(1, $types);
    }
}
