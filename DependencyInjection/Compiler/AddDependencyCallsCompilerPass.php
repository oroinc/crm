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
    const DATAGRID_MANAGER_TAG = 'oro_grid.datagrid.manager';
    const FLEXIBLE_CONFIG_PARAMETER = 'oro_flexibleentity.flexible_config';
    const FLEXIBLE_ENTITY_KEY = 'flexible';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds(self::DATAGRID_MANAGER_TAG) as $id => $tags) {
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
            'flexible_manager',
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

        if (isset($attributes['entity_hint'])) {
            $definition->addMethodCall('setEntityHint', array($attributes['entity_hint']));
        }

        // apply flexible configuration
        $this->applyFlexibleConfigurationFromAttributes($container, $serviceId, $attributes);
    }

    /**
     * This method read the attribute keys and configure grid manager class to use the related dependency
     *
     * @param ContainerBuilder $container
     * @param string $serviceId
     * @param array $attributes
     * @throws \LogicException
     */
    public function applyFlexibleConfigurationFromAttributes(
        ContainerBuilder $container,
        $serviceId,
        array $attributes
    ) {
        $definition = $container->getDefinition($serviceId);

        $managerSetter = 'setFlexibleManager';

        if ($definition->hasMethodCall($managerSetter) || empty($attributes[self::FLEXIBLE_ENTITY_KEY])) {
            return;
        }

        $entityKey = 'entity_name';
        $this->assertAttributesHasKey($serviceId, $attributes, $entityKey);

        $className = $attributes[$entityKey];
        if (!$container->hasParameter(self::FLEXIBLE_CONFIG_PARAMETER)) {
            throw new \LogicException(
                sprintf(
                    'Cannot get value of OroFlexibleEntityBundle configuration parameter ("%s").',
                    self::FLEXIBLE_CONFIG_PARAMETER
                )
            );
        }
        $flexibleConfig = $container->getParameter(self::FLEXIBLE_CONFIG_PARAMETER);

        // validate configuration
        if (!isset($flexibleConfig['entities_config'][$className]['flexible_manager'])) {
            throw new \LogicException(
                "Cannot get flexible manager of \"$className\" from entities configuration of OroFlexibleEntityBundle."
            );
        }

        $flexibleManagerServiceId = $flexibleConfig['entities_config'][$className]['flexible_manager'];
        $definition->addMethodCall($managerSetter, array(new Reference($flexibleManagerServiceId)));
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
        $arguments = array();
        if (!empty($attributes[self::FLEXIBLE_ENTITY_KEY])) {
            $factoryClass = '%oro_grid.orm.query_factory.entity.class%';

            $arguments[] = new Reference('doctrine');

            $this->assertAttributesHasKey($serviceId, $attributes, 'entity_name');
            $arguments[] = $attributes['entity_name'];

            if (!empty($attributes['query_entity_alias'])) {
                $arguments[] = $attributes['query_entity_alias'];
            }
        } else {
            $factoryClass = '%oro_grid.orm.query_factory.query.class%';
        }

        $definition = new Definition($factoryClass);
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
                    self::DATAGRID_MANAGER_TAG
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
