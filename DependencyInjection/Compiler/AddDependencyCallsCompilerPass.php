<?php

namespace Oro\Bundle\GridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;

use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;

class AddDependencyCallsCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('oro_grid.datagrid.manager') as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition = $container->getDefinition($id);

                $this->applyConfigurationFromAttributes($definition, $attributes);
                $this->applyDefaults($container, $id, $attributes);
            }
        }
    }

    /**
     * This method read the attribute keys and configure grid manager class to use the related dependency
     *
     * @param Definition $definition
     * @param array $attributes
     */
    public function applyConfigurationFromAttributes(Definition $definition, array $attributes)
    {
        $keys = array(
            'datagrid_builder',
            'list_builder',
            'query_factory',
            'translator',
            'validator',
            'request',
        );

        foreach ($keys as $key) {
            $method = 'set' . $this->camelize($key);
            if (!isset($attributes[$key]) || $definition->hasMethodCall($method)) {
                continue;
            }

            $definition->addMethodCall($method, array(new Reference($attributes[$key])));
        }
    }

    /**
     * Apply the default values required by the AdminInterface to the Admin service definition
     *
     * @param ContainerBuilder $container
     * @param string $serviceId
     * @param array $attributes
     * @internal param \Symfony\Component\DependencyInjection\Definition $definition
     * @return void
     */
    public function applyDefaults(
        ContainerBuilder $container,
        $serviceId,
        array $attributes
    ) {
        $definition = $container->getDefinition($serviceId);
        $definition->setScope(ContainerInterface::SCOPE_PROTOTYPE);

        $defaultAddServices = array(
            'query_factory'    => array($this, 'getDefaultQueryFactoryServiceId'),
            'datagrid_builder' => 'oro_grid.builder.datagrid',
            'list_builder'     => 'oro_grid.builder.list',
            'translator'       => 'translator',
            'validator'        => 'validator',
            'request'          => 'request',

        );

        foreach ($defaultAddServices as $attr => $addServiceId) {
            $method = 'set' . $this->camelize($attr);

            if (!$definition->hasMethodCall($method)) {
                if (is_callable($addServiceId)) {
                    $addServiceId = call_user_func($addServiceId, $container, $serviceId, $attributes);
                }
                $definition->addMethodCall($method, array(new Reference($addServiceId)));
            }
        }
    }

    /**
     * Get id of default query factory service
     *
     * @param ContainerBuilder $container
     * @param string $serviceId
     * @param array $attributes
     * @return string
     */
    protected function getDefaultQueryFactoryServiceId(ContainerBuilder $container, $serviceId, array $attributes)
    {
        $queryFactoryServiceId = sprintf('%s.default_query_factory', $serviceId);

        $container->setDefinition(
            $queryFactoryServiceId,
            $this->createDefaultQueryFactoryDefinition($serviceId, $attributes)
        );

        return $queryFactoryServiceId;
    }

    /**
     * Create default query factory service definition
     *
     * @param $serviceId
     * @param array $attributes
     * @return Definition
     * @throws InvalidDefinitionException
     */
    protected function createDefaultQueryFactoryDefinition($serviceId, array $attributes)
    {
        $arguments = array(new Reference('doctrine'));
        if (empty($attributes['query_entity'])) {
            throw new InvalidDefinitionException(
                sprintf(
                    'Definition of service "%s" must have "%s" attribute in tag "%s"',
                    $serviceId,
                    'query_entity',
                    'oro_grid.datagrid.manager'
                )
            );
        }
        $arguments[] = $attributes['query_entity'];

        if (!empty($attributes['query_entity_alias'])) {
            $arguments[] = $attributes['query_entity_alias'];
        }

        $definition = new Definition('%oro_grid.orm.query_factory.entity.class%');
        $definition->setPublic(false);
        $definition->setArguments($arguments);

        return $definition;
    }

    /**
     * Method taken from PropertyPath
     *
     * @param string $property
     * @return mixed
     */
    protected function camelize($property)
    {
        return Container::camelize($property);
    }
}
