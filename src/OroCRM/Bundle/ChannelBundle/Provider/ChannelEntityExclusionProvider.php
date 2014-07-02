<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\EntityBundle\Provider\ExclusionProviderInterface;

/**
 * This class is used by ChannelEntityChoiceType in order to show
 * only entities that could be included directly in channel(not through integration)
 */
class ChannelEntityExclusionProvider implements ExclusionProviderInterface
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
        // show channel entities that doesn't belongs to integrations
        if ($this->settingsProvider->isChannelEntity($className)) {
            return $this->settingsProvider->belongsToIntegration($className);
        }

        // all not related to channel entities will be ignored
        return true;
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
        return false;
    }
}
