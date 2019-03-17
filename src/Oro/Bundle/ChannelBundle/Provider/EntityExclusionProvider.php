<?php

namespace Oro\Bundle\ChannelBundle\Provider;

use Doctrine\ORM\Mapping\ClassMetadata;
use Oro\Bundle\EntityBundle\Provider\ExclusionProviderInterface;

/**
 * This class is registered as global exclusion provider in order to exclude entities
 * that are not included by any channel.
 */
class EntityExclusionProvider implements ExclusionProviderInterface
{
    /** @var SettingsProvider */
    private $settingsProvider;

    /** @var StateProvider */
    private $stateProvider;

    /**
     * @param SettingsProvider $settingsProvider
     * @param StateProvider    $stateProvider
     */
    public function __construct(SettingsProvider $settingsProvider, StateProvider $stateProvider)
    {
        $this->settingsProvider = $settingsProvider;
        $this->stateProvider = $stateProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function isIgnoredEntity($className)
    {
        return !$this->isIncludedByChannels($className);
    }

    /**
     * {@inheritdoc}
     */
    public function isIgnoredField(ClassMetadata $metadata, $fieldName)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isIgnoredRelation(ClassMetadata $metadata, $associationName)
    {
        return
            !$this->isIncludedByChannels($metadata->getName())
            || !$this->isIncludedByChannels($metadata->getAssociationTargetClass($associationName));
    }

    /**
     * @param string $entityClass
     *
     * @return bool
     */
    private function isIncludedByChannels($entityClass)
    {
        if ($this->settingsProvider->isChannelEntity($entityClass)) {
            return $this->stateProvider->isEntityEnabled($entityClass);
        }
        if ($this->settingsProvider->isDependentOnChannelEntity($entityClass)) {
            $enabled = false;
            $dependencies = $this->settingsProvider->getDependentEntities($entityClass);
            foreach ($dependencies as $entityName) {
                $enabled |= $this->stateProvider->isEntityEnabled($entityName);
            }

            return $enabled;
        }

        return true;
    }
}
