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
        // TODO Introduce a constant for tag
        foreach ($container->findTaggedServiceIds('oro_grid.datagrid.manager') as $id => $tags) {
            foreach ($tags as $attributes) {
                $this->applyConfigurationFromAttributes($container, $id, $attributes);
                $this->applyDefaults($container, $id, $attributes);
            }
        }
    }

    /**
     * This method read the attribute keys and configure grid manager class to use the related dependency
     *
     * @param ContainerBuilder $container
     * @param string $serviceId
     * @param array $attributes
     */
    public function applyConfigurationFromAttributes(
        ContainerBuilder $container,
        $serviceId,
        array $attributes
    ) {
        $definition = $container->getDefinition($serviceId);

        $keys = array(
            'query_factory',
            'route_generator',
            'datagrid_builder',
            'list_builder',
            'parameters',
            'translator',
            'validator',
        );

        foreach ($keys as $key) {
            $method = 'set' . $this->camelize($key);
            if (!isset($attributes[$key]) || $definition->hasMethodCall($method)) {
                continue;
            }

            $definition->addMethodCall($method, array(new Reference($attributes[$key])));
        }

        $this->assertAttributesHasKey($serviceId, $attributes, 'datagrid_name');
        $definition->addMethodCall('setName', array($attributes['datagrid_name']));

        // configure flexible manager parameters
        $this->applyFlexibleConfigurationFromAttributes($definition, $attributes);
    }

    /**
     * Configure specific flexible manager parameters
     *
     * @param Definition $definition
     * @param array $attributes
     */
    protected function applyFlexibleConfigurationFromAttributes(Definition $definition, array $attributes)
    {
        $code   = 'flexible_manager';
        $method = 'set' . $this->camelize($code);

        if (!isset($attributes[$code]) || $definition->hasMethodCall($method)) {
            return;
        }

        $definition->addMethodCall($method, array(new Reference($attributes[$code]), $attributes[$code]));
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
            'route_generator'  => array($this, 'getDefaultRouteGeneratorServiceId'),
            'parameters'       => array($this, 'getDefaultParametersServiceId'),
            'datagrid_builder' => 'oro_grid.builder.datagrid',
            'list_builder'     => 'oro_grid.builder.list',
            'translator'       => 'translator',
            'validator'        => 'validator',
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

        $this->assertAttributesHasKey($serviceId, $attributes, 'query_entity');

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
     * @param string $serviceId
     * @param string $attributes
     * @param string $key
     * @throws InvalidDefinitionException
     */
    private function assertAttributesHasKey($serviceId, array $attributes, $key)
    {
        if (empty($attributes[$key])) {
            throw new InvalidDefinitionException(
                sprintf(
                    'Definition of service "%s" must have "%s" attribute in tag "%s"',
                    $serviceId,
                    $key,
                    'oro_grid.datagrid.manager'
                )
            );
        }
    }

    /**
     * Get id of default route generator service
     *
     * @param ContainerBuilder $container
     * @param string $serviceId
     * @param array $attributes
     * @return string
     */
    protected function getDefaultRouteGeneratorServiceId(ContainerBuilder $container, $serviceId, array $attributes)
    {
        $routeGeneratorServiceId = sprintf('%s.route.default_generator', $serviceId);

        $container->setDefinition(
            $routeGeneratorServiceId,
            $this->createDefaultRouteGeneratorDefinition($serviceId, $attributes)
        );

        return $routeGeneratorServiceId;
    }

    /**
     * Create default query factory service definition
     *
     * @param $serviceId
     * @param array $attributes
     * @return Definition
     * @throws InvalidDefinitionException
     */
    protected function createDefaultRouteGeneratorDefinition($serviceId, array $attributes)
    {
        $arguments = array(new Reference('router'));

        $this->assertAttributesHasKey($serviceId, $attributes, 'route_name');

        $arguments[] = $attributes['route_name'];

        $definition = new Definition('%oro_grid.route.default_generator.class%');
        $definition->setPublic(false);
        $definition->setArguments($arguments);

        return $definition;
    }

    /**
     * Get id of default parameters service
     *
     * @param ContainerBuilder $container
     * @param string $serviceId
     * @param array $attributes
     * @return string
     */
    protected function getDefaultParametersServiceId(ContainerBuilder $container, $serviceId, array $attributes)
    {
        $routeGeneratorServiceId = sprintf('%s.parameters.default', $serviceId);

        $container->setDefinition(
            $routeGeneratorServiceId,
            $this->createDefaultParametersDefinition($serviceId, $attributes)
        );

        return $routeGeneratorServiceId;
    }

    /**
     * Create default query factory service definition
     *
     * @param $serviceId
     * @param array $attributes
     * @return Definition
     * @throws InvalidDefinitionException
     */
    protected function createDefaultParametersDefinition($serviceId, array $attributes)
    {
        $arguments = array(new Reference('service_container'));

        $this->assertAttributesHasKey($serviceId, $attributes, 'datagrid_name');

        $arguments[] = $attributes['datagrid_name'];

        $definition = new Definition('%oro_grid.datagrid.parameters.class%');
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
