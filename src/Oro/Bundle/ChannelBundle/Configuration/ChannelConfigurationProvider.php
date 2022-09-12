<?php

namespace Oro\Bundle\ChannelBundle\Configuration;

use Oro\Component\Config\Cache\PhpArrayConfigProvider;
use Oro\Component\Config\Loader\CumulativeConfigProcessorUtil;
use Oro\Component\Config\Loader\Factory\CumulativeConfigLoaderFactory;
use Oro\Component\Config\Resolver\ResolverInterface;
use Oro\Component\Config\ResourcesContainerInterface;

/**
 * The provider for configuration that is loaded from "Resources/config/oro/channels.yml" files.
 */
class ChannelConfigurationProvider extends PhpArrayConfigProvider
{
    private const CONFIG_FILE = 'Resources/config/oro/channels.yml';

    private const ENTITY_DATA       = 'entity_data';
    private const DEPENDENT_MAP     = 'dependent_map';
    private const CHANNEL_TYPES     = 'channel_types';
    private const CUSTOMER_ENTITIES = 'customer_entities';
    private const NAME              = 'name';
    private const DEPENDENT         = 'dependent';
    private const CUSTOMER_IDENTITY = 'customer_identity';

    private ResolverInterface $resolver;

    public function __construct(string $cacheFile, bool $debug, ResolverInterface $resolver)
    {
        parent::__construct($cacheFile, $debug);
        $this->resolver = $resolver;
    }

    /**
     * Gets configuration of entities.
     *
     * @return array [entity class => config, ....]
     */
    public function getEntities(): array
    {
        $config = $this->doGetConfig();

        return $config[self::ENTITY_DATA];
    }

    /**
     * Gets configuration of dependent entities.
     *
     * @return array [dependent entity class => [entity class, ...], ....]
     */
    public function getDependentEntitiesMap(): array
    {
        $config = $this->doGetConfig();

        return $config[self::DEPENDENT_MAP];
    }

    /**
     * Gets configuration of channel types.
     *
     * @return array [channel type => config, ....]
     */
    public function getChannelTypes(): array
    {
        $config = $this->doGetConfig();

        return $config[self::CHANNEL_TYPES];
    }

    /**
     * Gets class names of customer entities.
     *
     * @return string[]
     */
    public function getCustomerEntities(): array
    {
        $config = $this->doGetConfig();

        return $config[self::CUSTOMER_ENTITIES];
    }

    /**
     * {@inheritdoc}
     */
    protected function doLoadConfig(ResourcesContainerInterface $resourcesContainer)
    {
        $configs = [];
        $configLoader = CumulativeConfigLoaderFactory::create('oro_channels', self::CONFIG_FILE);
        $resources = $configLoader->load($resourcesContainer);
        foreach ($resources as $resource) {
            if (!empty($resource->data[ChannelConfiguration::ROOT_NODE])) {
                $configs[] = $resource->data[ChannelConfiguration::ROOT_NODE];
            }
        }

        return $this->resolveConfig(CumulativeConfigProcessorUtil::processConfiguration(
            self::CONFIG_FILE,
            new ChannelConfiguration(),
            $configs
        ));
    }

    private function resolveConfig(array $config): array
    {
        $resolvedConfig = $this->resolver->resolve($config);

        $entities = $this->buildEntities($resolvedConfig);
        $resolvedConfig[self::ENTITY_DATA] = $entities;
        $resolvedConfig[self::DEPENDENT_MAP] = $this->buildDependentEntitiesMap($entities);
        if (!\array_key_exists(self::CHANNEL_TYPES, $resolvedConfig)) {
            $resolvedConfig[self::CHANNEL_TYPES] = [];
        }
        $channelTypes = $resolvedConfig[self::CHANNEL_TYPES];
        $resolvedConfig[self::CUSTOMER_ENTITIES] = $this->buildCustomerEntities($channelTypes);

        return $resolvedConfig;
    }

    private function buildEntities(array $config): array
    {
        $entities = [];
        if (!empty($config[self::ENTITY_DATA])) {
            foreach ($config[self::ENTITY_DATA] as $entity) {
                $entities[$entity[self::NAME]] = $entity;
            }
        }

        return $entities;
    }

    private function buildDependentEntitiesMap(array $entities): array
    {
        $map = [];
        foreach ($entities as $entity) {
            if (!empty($entity[self::DEPENDENT])) {
                foreach ($entity[self::DEPENDENT] as $entityName) {
                    $map[$entityName][] = $entity[self::NAME];
                }
            }
        }

        return $map;
    }

    /**
     * @param array $channelTypes
     *
     * @return string[]
     */
    private function buildCustomerEntities(array $channelTypes): array
    {
        $customerEntities = [];
        foreach ($channelTypes as $config) {
            if (!empty($config[self::CUSTOMER_IDENTITY])) {
                $customerEntities[] = $config[self::CUSTOMER_IDENTITY];
            }
        }

        return \array_values(\array_unique($customerEntities));
    }
}
