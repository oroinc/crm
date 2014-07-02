<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\EntityBundle\Provider\ExclusionProviderInterface;

class EntityExclusionProvider implements ExclusionProviderInterface
{
    /** @var SettingsProvider */
    protected $settingsProvider;

    /**
     * @param SettingsProvider $settingsProvider
     */
    public function __construct(SettingsProvider $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
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
        return !$this->isIncludedByChannels($metadata->getAssociationTargetClass($associationName));
    }

    /**
     * @param $entityFQCN
     *
     * @return bool
     */
    protected function isIncludedByChannels($entityFQCN)
    {
        if (!($this->settingsProvider->isChannelEntity($entityFQCN)
            || $this->settingsProvider->isDependentOnChannelEntity($entityFQCN))
        ) {
            return true;
        }

        // TODO check if it's in any integration
        // TODO check if it's enabled in any channel
    }
}
