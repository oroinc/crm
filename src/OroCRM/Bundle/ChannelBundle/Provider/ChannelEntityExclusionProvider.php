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
        return !$this->settingsProvider->isChannelEntity($className);
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
