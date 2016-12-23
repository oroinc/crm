<?php

namespace Oro\Bundle\ChannelBundle\Provider;

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
     * Return whether given entity is related to customer
     *
     * @param string $entityFQCN entity full class name
     *
     * @return bool
     */
    public function isCustomerEntity($entityFQCN)
    {
        $settings = $this->getSettings(self::CHANNEL_TYPE_PATH);
        $classes = array_map(function ($item) {
            return $item['customer_identity'];
        }, $settings);

        return in_array($entityFQCN, $classes, true);
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
     * Returns integration types that could not be used out of channel scope
     *
     * @return array
     */
    public function getSourceIntegrationTypes()
    {
        $settings = $this->getSettings(self::CHANNEL_TYPE_PATH);
        $types    = [];

        if (is_array($settings)) {
            foreach (array_keys($settings) as $channelTypeName) {
                $integrationType = $this->getIntegrationType($channelTypeName);
                if ($integrationType) {
                    $types[] = $integrationType;
                }
            }
        }

        return array_unique($types);
    }

    /**
     * Returns channel types that could be used in channel type selector
     * sorted by priority
     *
     * @return array
     */
    public function getChannelTypeChoiceList()
    {
        $settings     = $this->getSettings(self::CHANNEL_TYPE_PATH);
        $channelTypes = [];

        uasort(
            $settings,
            function ($firstArray, $secondArray) {
                if ($firstArray['priority'] == $secondArray['priority']) {
                    return 0;
                }

                return ($firstArray['priority'] < $secondArray['priority']) ? -1 : 1;
            }
        );

        foreach (array_keys($settings) as $channelTypeName) {
            $channelTypes[$channelTypeName] = $settings[$channelTypeName]['label'];
        }

        return $channelTypes;
    }

    /**
     * Returns channel types that could be used in channel type selector
     * sorted by priority
     *
     * @return array
     */
    public function getNonSystemChannelTypeChoiceList()
    {
        $channelTypes = array_filter($this->getChannelTypeChoiceList(), function ($channelTypeName) {
            return !$this->isChannelSystem($channelTypeName);
        }, ARRAY_FILTER_USE_KEY);

        return $channelTypes;
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
     * Check system status of channel
     *
     * @param string $channelType
     *
     * @return bool
     *
     * @throws \LogicException If channel type config not found
     */
    public function isChannelSystem($channelType)
    {
        $settings = $this->getSettings(self::CHANNEL_TYPE_PATH);

        if (!isset($settings[$channelType])) {
            throw new \LogicException(sprintf('Unable to find "%s" channel type\'s config', $channelType));
        }

        return !empty($settings[$channelType]['system'])
            ? $settings[$channelType]['system'] : false;
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
     * Get CustomerIdentity definition from config
     *
     * @param $type
     *
     * @return string|null
     */
    public function getCustomerIdentityFromConfig($type)
    {
        return $this->getChannelTypeConfig($type, 'customer_identity');
    }

    /**
     * Returns predefined entity list for given channel type
     *
     * @param string $type
     *
     * @return array
     */
    public function getEntitiesByChannelType($type)
    {
        return $this->getChannelTypeConfig($type, 'entities') ?: [];
    }

    /**
     * @param string      $type
     * @param string|null $block
     *
     * @return mixed|null
     */
    protected function getChannelTypeConfig($type, $block = null)
    {
        $settings = $this->getSettings(self::CHANNEL_TYPE_PATH);

        if (null === $block) {
            $config = isset($settings[$type]) ? $settings[$type] : null;
        } else {
            $config = isset($settings[$type], $settings[$type][$block]) ? $settings[$type][$block] : null;
        }

        return $config;
    }

    /**
     * @return array
     */
    public function getLifetimeValueSettings()
    {
        $settings = $this->getSettings(self::CHANNEL_TYPE_PATH);
        $result   = [];

        foreach ($settings as $channelType => $setting) {
            if (!empty($setting['lifetime_value'])) {
                $result[$channelType] = [
                    'entity' => $setting['customer_identity'],
                    'field'  => $setting['lifetime_value']
                ];
            }
        }

        return $result;
    }
}
