<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Oro\Component\Config\Resolver\ResolverInterface;

class SettingsProvider
{
    const DATA_PATH         = 'entity_data';
    const CHANNEL_TYPE_PATH = 'channel_types';

    /** @var array */
    protected $settings = [];

    /** @var null|array */
    protected $resolvedSettings;

    /** @var ResolverInterface */
    protected $resolver;

    /** @var null|array */
    protected $dependentEntitiesHashMap;

    /**
     * @param array             $settings
     * @param ResolverInterface $resolver
     */
    public function __construct(array $settings, ResolverInterface $resolver)
    {
        $this->settings = $settings;
        $this->resolver = $resolver;
    }

    /**
     * Get settings that were collected from channel_configuration config files
     *
     * @param null $section
     *
     * @return array|null
     */
    public function getSettings($section = null)
    {
        if (null === $this->resolvedSettings) {
            $settings = $this->resolvedSettings = $this->resolver->resolve($this->settings);
            $this->resolvedSettings[self::DATA_PATH] = [];
            foreach ($settings[self::DATA_PATH] as $singleEntitySetting) {
                $this->resolvedSettings[self::DATA_PATH][trim($singleEntitySetting['name'])] = $singleEntitySetting;
            }
        }

        if ($section === null) {
            return $this->resolvedSettings;
        } elseif (isset($this->resolvedSettings[$section])) {
            return $this->resolvedSettings[$section];
        }

        return null;
    }

    /**
     * Return whether given entity is related to channel
     *
     * @param string $entityFQCN entity full class name
     *
     * @return bool
     */
    public function isChannelEntity($entityFQCN)
    {
        $settings = $this->getSettings(self::DATA_PATH);

        return array_key_exists($entityFQCN, $settings);
    }

    /**
     * Return whether entity dependent to any business entity
     *
     * @param string $entityFQCN entity full class name
     *
     * @return bool
     */
    public function isDependentOnChannelEntity($entityFQCN)
    {
        return $this->getDependentEntityData($entityFQCN) !== false;
    }

    /**
     * Return whether entity is channel related and belongs to any integration
     *
     * @param string $entityFQCN entity full class name
     *
     * @return bool
     */
    public function belongsToIntegration($entityFQCN)
    {
        return $this->getIntegrationTypeData($entityFQCN) !== false;
    }

    /**
     * Get entity dependencies
     *
     * @param string $entityFQCN entity full class name
     *
     * @return bool|array
     */
    public function getDependentEntityData($entityFQCN)
    {
        if (null === $this->dependentEntitiesHashMap) {
            $settings = $this->getSettings(self::DATA_PATH);

            foreach ($settings as $singleEntityData) {
                if (empty($singleEntityData['dependent'])) {
                    continue;
                }

                $dependentEntities = array_values($singleEntityData['dependent']);
                foreach ($dependentEntities as $entityName) {
                    $entityName = trim($entityName);

                    if (!isset($this->dependentEntitiesHashMap[$entityName])) {
                        $this->dependentEntitiesHashMap[$entityName] = [];
                    }

                    $this->dependentEntitiesHashMap[$entityName][] = trim($singleEntityData['name']);
                }
            }
        }

        return isset($this->dependentEntitiesHashMap[$entityFQCN])
            ? $this->dependentEntitiesHashMap[$entityFQCN]
            : false;
    }

    /**
     * Returns integration type that entity belongs to
     *
     * @param string $entityFQCN entity full class name
     *
     * @return bool|string
     */
    public function getIntegrationTypeData($entityFQCN)
    {
        if (!$this->isChannelEntity($entityFQCN)) {
            return false;
        }

        $settings = $this->getSettings(self::DATA_PATH);

        return isset($settings[$entityFQCN]['belongs_to'], $settings[$entityFQCN]['belongs_to']['integration']) ?
            $settings[$entityFQCN]['belongs_to']['integration'] : false;
    }

    /**
     * Returns integration types that could be used as customer datasource
     *
     * @return array
     */
    public function getSourceIntegrationTypes()
    {
        $settings     = $this->getSettings(self::DATA_PATH);
        $allowedTypes = [];

        foreach (array_keys($settings) as $entityName) {
            if ($this->belongsToIntegration($entityName)) {
                $allowedTypes[] = $this->getIntegrationTypeData($entityName);
            }
        }

        return array_unique($allowedTypes);
    }

    /**
     * Returns channel types that could be used in channel type selector
     *
     * @return array
     */
    public function getChannelTypeChoiceList()
    {
        $settings     = $this->getSettings(self::CHANNEL_TYPE_PATH);
        $channelTypes = [];

        foreach (array_keys($settings) as $channelTypeName) {
            $channelTypes[$channelTypeName] = $settings[$channelTypeName]['label'];
        }

        return $channelTypes;
    }

    /**
     * Returns all entities from config which placed in field name
     *
     * @return array
     */
    public function getChannelEntitiesChoiceList()
    {
        $settings = $this->getSettings(self::DATA_PATH);
        $result   = [];

        foreach ($settings as $setting) {
            $result[] = $setting['name'];
        }

        return $result;
    }

    /**
     * Get required integration type for given channel type
     *
     * @param string $channelType
     *
     * @return bool|string     Returns false if channel type does not require to include integration,
     *                         integration type otherwise
     * @throws \LogicException If channel type config not found
     */
    public function getIntegrationType($channelType)
    {
        $settings = $this->getSettings(self::CHANNEL_TYPE_PATH);

        if (!isset($settings[$channelType])) {
            throw new \LogicException(sprintf('Unable to find "%s" channel type\'s config', $channelType));
        }

        return !empty($settings[$channelType]['integration_type'])
            ? $settings[$channelType]['integration_type'] : false;
    }

    /**
     * Returns integration connector name that entity belongs to
     *
     * @param string $entityFQCN entity full class name
     *
     * @return bool|string
     */
    public function getIntegrationConnectorName($entityFQCN)
    {
        if (!$this->isChannelEntity($entityFQCN)) {
            return false;
        }

        $settings = $this->getSettings(self::DATA_PATH);

        return isset($settings[$entityFQCN]['belongs_to'], $settings[$entityFQCN]['belongs_to']['connector']) ?
            $settings[$entityFQCN]['belongs_to']['connector'] : false;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isCustomerIdentityUserDefined($type)
    {
        $settings = $this->getSettings(self::CHANNEL_TYPE_PATH);

        return isset($settings[$type]['is_customer_identity_user_defined'])
            ? $settings[$type]['is_customer_identity_user_defined']
            : true;
    }

    /**
     * Get CustomerIdentity definition from config
     *
     * @param $type
     *
     * @return string|null
     */
    public function getCustomerIdentityFromConfig($type)
    {
        $settings = $this->getSettings(self::CHANNEL_TYPE_PATH);

        return !empty($settings[$type]['customer_identity'])
            ? $settings[$type]['customer_identity']
            : null;
    }
}
