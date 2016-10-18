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
    protected $settingsProvider;

    /** @var StateProvider */
    protected $stateProvider;

    /**
     * @param SettingsProvider $settingsProvider
     * @param StateProvider    $stateProvider
     */
    public function __construct(SettingsProvider $settingsProvider, StateProvider $stateProvider)
    {
        $this->settingsProvider = $settingsProvider;
        $this->stateProvider    = $stateProvider;
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
        $isMainEntityIncluded = $this->isIncludedByChannels($metadata->getName());
        $isRelationIncluded   = $this->isIncludedByChannels($metadata->getAssociationTargetClass($associationName));

        return !($isMainEntityIncluded && $isRelationIncluded);
    }

    /**
     * @param string $entityFQCN entity full class name
     *
     * @return bool
     */
    protected function isIncludedByChannels($entityFQCN)
    {
        if ($this->settingsProvider->isChannelEntity($entityFQCN)) {
            return $this->stateProvider->isEntityEnabled($entityFQCN);
        } elseif ($this->settingsProvider->isDependentOnChannelEntity($entityFQCN)) {
            $enabled      = false;
            $dependencies = $this->settingsProvider->getDependentEntityData($entityFQCN);
            foreach ($dependencies as $entityName) {
                $enabled |= $this->stateProvider->isEntityEnabled($entityName);
            }

            return $enabled;
        }

        return true;
    }
}
