<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Oro\Component\Config\Resolver\ResolverInterface;

class SettingsProvider
{
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
            $this->resolvedSettings['entity_data'] = [];
            foreach ($settings['entity_data'] as $singleEntitySetting) {
                $this->resolvedSettings['entity_data'][trim($singleEntitySetting['name'])] = $singleEntitySetting;
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
        $settings = $this->getSettings('entity_data');

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
            $settings = $this->getSettings('entity_data');

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

        $settings = $this->getSettings('entity_data');

        return !empty($settings[$entityFQCN]['belongs_to_integration']) ?
            $settings[$entityFQCN]['belongs_to_integration'] : false;
    }

    /**
     * Returns integration types that could be used as customer datasource
     *
     * @return array
     */
    public function getSourceIntegrationTypes()
    {
        $settings     = $this->getSettings('entity_data');
        $allowedTypes = [];

        foreach (array_keys($settings) as $entityName) {
            if ($this->belongsToIntegration($entityName)) {
                $allowedTypes[] = $this->getIntegrationTypeData($entityName);
            }
        }

        return array_unique($allowedTypes);
    }
}
