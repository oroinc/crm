<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Symfony\Component\Routing\RouterInterface;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;

class MetadataProvider implements MetadataProviderInterface
{
    const ENTITY_EDIT_ROUTE = 'oro_entityconfig_update';
    const ENTITY_VIEW_ROUTE = 'oro_entityconfig_view';

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
     * @param RouterInterface  $router
     */
    public function __construct(
        SettingsProvider $settings,
        EntityProvider $entityProvider,
        ConfigManager $configManager,
        RouterInterface $router
    ) {
        $this->settings       = $settings;
        $this->entityProvider = $entityProvider;
        $this->configManager  = $configManager;
        $this->router         = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntitiesMetadata()
    {
        $result = [];

        foreach ($this->settings->getSettings(SettingsProvider::DATA_PATH) as $setting) {
            $entityConfig      = $this->entityProvider->getEntity($setting['name'], true);
            $configEntityModel = $this->configManager->getConfigEntityModel($setting['name']);

            if ($configEntityModel instanceof EntityConfigModel) {
                $entityConfig = array_merge($entityConfig, $this->getEntityLinks($configEntityModel));
            }

            $result[$setting['name']] = $entityConfig;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getIntegrationEntities()
    {
        $result = [];

        foreach ($this->settings->getSettings(SettingsProvider::DATA_PATH) as $setting) {
            $integration = isset($setting['belongs_to']['integration']) ? $setting['belongs_to']['integration'] : false;
            if (false !== $integration) {
                $result[$integration]   = isset($result[$integration]) ? $result[$integration] : [];
                $result[$integration][] = $setting['name'];
            }
        }

        return $result;
    }

    /**
     * @param EntityConfigModel $configEntityModel
     *
     * @return array
     */
    protected function getEntityLinks(EntityConfigModel $configEntityModel)
    {
        return [
            'entity_id' => $configEntityModel->getId(),
            'edit_link' => $this->generateUrl(self::ENTITY_EDIT_ROUTE, ['id' => $configEntityModel->getId()]),
            'view_link' => $this->generateUrl(self::ENTITY_VIEW_ROUTE, ['id' => $configEntityModel->getId()]),
        ];
    }

    /**
     * @param string $route
     * @param array  $parameters
     *
     * @return string The generated URL
     */
    protected function generateUrl($route, $parameters = [])
    {
        return $this->router->generate($route, $parameters);
    }
}
