<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Oro\Bundle\EntityBundle\Provider\AbstractExclusionProvider;

/**
 * This class is used by ChannelEntityChoiceType in order to show
 * only entities that could be included directly in channel(not through integration)
 */
class ChannelEntityExclusionProvider extends AbstractExclusionProvider
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
}
