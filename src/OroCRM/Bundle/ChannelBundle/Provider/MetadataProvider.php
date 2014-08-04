<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;

class MetadataProvider implements MetadataInterface
{
    /** @var SettingsProvider */
    protected $settings;

    /** @var EntityProvider */
    protected $entityProvider;

    /** @var ConfigManager */
    protected $configManager;

    /**
     * @param SettingsProvider $settings
     * @param EntityProvider   $entityProvider
     * @param ConfigManager    $configManager
     */
    public function __construct(
        SettingsProvider $settings,
        EntityProvider $entityProvider,
        ConfigManager $configManager
    ) {
        $this->settings       = $settings;
        $this->entityProvider = $entityProvider;
        $this->configManager  = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataList()
    {
        $result = [];

        foreach ($this->settings->getSettings(SettingsProvider::DATA_PATH) as $setting) {
            if (!empty($setting['belongs_to']['integration'])) {
                $entityConfig      = $this->entityProvider->getEntity($setting['name'], true);
                $configEntityModel = $this->configManager->getConfigEntityModel($setting['name']);

                if ($configEntityModel instanceof EntityConfigModel) {
                    $entityConfig['entity_id'] = $configEntityModel->getId();
                }

                $result[$setting['belongs_to']['integration']][] = $entityConfig;
            }
        }

        return $result;
    }
}
