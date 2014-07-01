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

    /** @var null|array */
    protected $channelEntitiesHashMap;

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
     * @return array|null
     */
    public function getChannelSettings()
    {
        if (null === $this->resolvedSettings) {
            $this->resolvedSettings = $this->resolver->resolve($this->settings);
        }

        return $this->resolvedSettings;
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
        if (null === $this->channelEntitiesHashMap) {
            $settings                     = $this->getChannelSettings();
            $this->channelEntitiesHashMap = array_map(
                function ($singleEntitySetting) {
                    return trim($singleEntitySetting['name']);
                },
                $settings['entity_data']
            );
        }

        return in_array($entityFQCN, $this->channelEntitiesHashMap, true);
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
    protected function getDependentEntityData($entityFQCN)
    {
        if (null === $this->dependentEntitiesHashMap) {
            $settings = $this->getChannelSettings();

            foreach ($settings['entity_data'] as $singleEntityData) {
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
}
