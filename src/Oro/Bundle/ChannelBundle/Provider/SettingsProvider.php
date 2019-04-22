<?php

namespace Oro\Bundle\ChannelBundle\Provider;

use Oro\Bundle\ChannelBundle\Configuration\ChannelConfigurationProvider;

/**
 * The provider for different kind of channel settings.
 */
class SettingsProvider
{
    private const INTEGRATION_TYPE  = 'integration_type';
    private const CUSTOMER_IDENTITY = 'customer_identity';
    private const LIFETIME_VALUE    = 'lifetime_value';
    private const PRIORITY          = 'priority';

    /** @var ChannelConfigurationProvider */
    private $configProvider;

    /**
     * @param ChannelConfigurationProvider $configProvider
     */
    public function __construct(ChannelConfigurationProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * Gets configuration of channel types.
     *
     * @return array [channel type => channel config, ...]
     */
    public function getChannelTypes(): array
    {
        return $this->configProvider->getChannelTypes();
    }

    /**
     * Gets configuration of entities.
     *
     * @return array [entity class => entity config, ...]
     */
    public function getEntities(): array
    {
        return $this->configProvider->getEntities();
    }

    /**
     * Checks if the given entity is related to any channel.
     *
     * @param string $entityClass
     *
     * @return bool
     */
    public function isChannelEntity(string $entityClass): bool
    {
        $entities = $this->configProvider->getEntities();

        return \array_key_exists($entityClass, $entities);
    }

    /**
     * Checks if the given entity is related to any customer.
     *
     * @param string $entityClass
     *
     * @return bool
     */
    public function isCustomerEntity(string $entityClass): bool
    {
        return \in_array($entityClass, $this->configProvider->getCustomerEntities(), true);
    }

    /**
     * Checks if the given entity dependents on any business entity.
     *
     * @param string $entityClass
     *
     * @return bool
     */
    public function isDependentOnChannelEntity(string $entityClass): bool
    {
        $dependentEntitiesMap = $this->configProvider->getDependentEntitiesMap();

        return isset($dependentEntitiesMap[$entityClass]);
    }

    /**
     * Gets dependencies for the given entity.
     *
     * @param string $entityClass
     *
     * @return array
     */
    public function getDependentEntities(string $entityClass): array
    {
        $dependentEntitiesMap = $this->configProvider->getDependentEntitiesMap();

        return $dependentEntitiesMap[$entityClass] ?? [];
    }

    /**
     * Gets integration types that could not be used out of channel scope.
     *
     * @return string[]
     */
    public function getSourceIntegrationTypes(): array
    {
        $types = [];

        $channelTypes = $this->configProvider->getChannelTypes();
        foreach ($channelTypes as $config) {
            if (isset($config[self::INTEGRATION_TYPE])) {
                $types[] = $config[self::INTEGRATION_TYPE];
            }
        }

        return \array_values(\array_unique($types));
    }

    /**
     * Gets channel types that could be used in channel type selector.
     * The returned channel types are sorted by priority.
     *
     * @return array
     */
    public function getChannelTypeChoiceList(): array
    {
        $result = [];

        $channelTypes = $this->configProvider->getChannelTypes();
        \uasort(
            $channelTypes,
            function ($firstArray, $secondArray) {
                if ($firstArray[self::PRIORITY] === $secondArray[self::PRIORITY]) {
                    return 0;
                }

                return ($firstArray[self::PRIORITY] < $secondArray[self::PRIORITY]) ? -1 : 1;
            }
        );
        foreach (\array_keys($channelTypes) as $channelType) {
            $result[$channelTypes[$channelType]['label']] = $channelType;
        }

        return $result;
    }

    /**
     * Gets not system channel types that could be used in channel type selector.
     * The returned channel types are sorted by priority.
     *
     * @return array
     */
    public function getNonSystemChannelTypeChoiceList(): array
    {
        return \array_filter(
            $this->getChannelTypeChoiceList(),
            function ($channelType) {
                return !$this->isSystemChannel($channelType);
            }
        );
    }

    /**
     * Get required integration type for given channel type
     *
     * @param string $channelType
     *
     * @return string|null The integration type
     *                     or FALSE if the given channel type does not require to include integration
     */
    public function getIntegrationType(string $channelType): ?string
    {
        $channelTypes = $this->configProvider->getChannelTypes();
        if (!isset($channelTypes[$channelType])) {
            throw new \LogicException(sprintf('The channel "%s" is not defined.', $channelType));
        }

        return $channelTypes[$channelType][self::INTEGRATION_TYPE] ?? null;
    }

    /**
     * Checks whether the given channel is a system channel or not.
     *
     * @param string $channelType
     *
     * @return bool
     */
    public function isSystemChannel(string $channelType): bool
    {
        $channelTypes = $this->configProvider->getChannelTypes();
        if (!isset($channelTypes[$channelType])) {
            throw new \LogicException(sprintf('The channel "%s" is not defined.', $channelType));
        }

        return $channelTypes[$channelType]['system'] ?? false;
    }

    /**
     * Gets the name of integration connector to which the given entity belongs to.
     *
     * @param string $entityClass entity full class name
     *
     * @return string|null
     */
    public function getIntegrationConnectorName(string $entityClass): ?string
    {
        $entities = $this->configProvider->getEntities();

        return $entities[$entityClass]['belongs_to']['connector'] ?? null;
    }

    /**
     * Gets CustomerIdentity definition from config.
     *
     * @param string $type
     *
     * @return string|null
     */
    public function getCustomerIdentityFromConfig(string $type): ?string
    {
        $channelTypes = $this->configProvider->getChannelTypes();

        return $channelTypes[$type][self::CUSTOMER_IDENTITY] ?? null;
    }

    /**
     * Gets predefined entity list for given channel type.
     *
     * @param string $type
     *
     * @return array
     */
    public function getEntitiesByChannelType(string $type): array
    {
        $channelTypes = $this->configProvider->getChannelTypes();

        return $channelTypes[$type]['entities'] ?? [];
    }

    /**
     * @return array
     */
    public function getLifetimeValueSettings(): array
    {
        $result = [];

        $channelTypes = $this->configProvider->getChannelTypes();
        foreach ($channelTypes as $channelType => $setting) {
            if (!empty($setting[self::LIFETIME_VALUE])) {
                $result[$channelType] = [
                    'entity' => $setting[self::CUSTOMER_IDENTITY],
                    'field'  => $setting[self::LIFETIME_VALUE]
                ];
            }
        }

        return $result;
    }
}
