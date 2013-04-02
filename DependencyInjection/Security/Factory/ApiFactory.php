<?php

namespace Oro\Bundle\NavigationBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

class ApiFactory implements SecurityFactoryInterface
{
    public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
    {
        // Change context for ContextListener which
        if (!empty($config['parent_context'])) {
            $securityContextProviderId = 'security.context_listener.0';
            $container->setDefinition(
                $securityContextProviderId,
                new DefinitionDecorator('security.context_listener')
            )->replaceArgument(2, $config['parent_context']);
        }

        $providerId = 'security.authentication.provider.dao.' . $id;
        $container->setDefinition(
            $providerId,
            new DefinitionDecorator('security.authentication.provider.dao')
        )->replaceArgument(0, new Reference($userProvider))->replaceArgument(2, $id);

        // entry point
        $entryPointId = $this->createEntryPoint($container, $id, $defaultEntryPoint);

        $listenerId = 'api.security.authentication.listener.' . $id;
        $listener = $container->setDefinition(
            $listenerId,
            new DefinitionDecorator('api.security.authentication.listener')
        );
        $listener->replaceArgument(2, $id);
        $listener->replaceArgument(3, new Reference($entryPointId));

        return array($providerId, $listenerId, $defaultEntryPoint);
    }

    public function getPosition()
    {
        return 'form';
    }

    public function getKey()
    {
        return 'api';
    }

    public function addConfiguration(NodeDefinition $node)
    {
        $node->children()
            ->scalarNode('parent_context')->defaultValue('')
            ->end();
    }

    protected function createEntryPoint(ContainerBuilder $container, $id, $defaultEntryPoint)
    {
        if (null !== $defaultEntryPoint) {
            return $defaultEntryPoint;
        }

        $entryPointId = 'api.security.authentication.entry_point.' . $id;
        $container->setDefinition($entryPointId, new DefinitionDecorator('api.security.authentication.entry_point'));

        return $entryPointId;
    }
}
