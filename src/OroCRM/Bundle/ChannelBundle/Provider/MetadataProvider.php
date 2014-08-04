<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;

class MetadataProvider implements MetadataInterface
{
    /** @var SettingsProvider */
    protected $settings;

    /** @var EntityProvider */
    protected $entityProvider;

    /**
     * @param SettingsProvider       $settings
     * @param EntityProvider         $entityProvider
     */
    public function __construct(SettingsProvider $settings, EntityProvider $entityProvider)
    {
        $this->settings = $settings;
        $this->entityProvider = $entityProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataList()
    {
        $result = [];

        foreach ($this->settings->getSettings(SettingsProvider::DATA_PATH) as $setting) {
            if (!empty($setting['belongs_to']['integration'])) {
                $result[$setting['belongs_to']['integration']][] = $this->entityProvider
                    ->getEntity($setting['name'], true);
            }
        }

        return $result;
    }
}
