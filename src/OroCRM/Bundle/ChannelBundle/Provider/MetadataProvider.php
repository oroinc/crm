<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;

class MetadataProvider implements MetadataInterface
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
    public function getMetadataList()
    {
        $result = [];

        foreach ($this->settings->getSettings(SettingsProvider::DATA_PATH) as $setting) {
            if (!empty($setting['belongs_to']['integration'])) {
                $entityConfig      = $this->entityProvider->getEntity($setting['name'], true);
                $configEntityModel = $this->configManager->getConfigEntityModel($setting['name']);

                if ($configEntityModel instanceof EntityConfigModel) {
                    $entityConfig['entity_id'] = $configEntityModel->getId();
                    $entityConfig['edit_link'] = $this->generateUrl(
                        self::ENTITY_EDIT_ROUTE,
                        ['id' => $configEntityModel->getId()]
                    );
                    $entityConfig['view_link'] = $this->generateUrl(
                        self::ENTITY_VIEW_ROUTE,
                        ['id' => $configEntityModel->getId()]
                    );
                }

                $result[$setting['belongs_to']['integration']][] = $entityConfig;
            }
        }

        return $result;
    }

    /**
     * @param string      $route
     * @param array       $parameters
     * @param bool|string $referenceType
     *
     * @return string The generated URL
     */
    protected function generateUrl($route, $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->router->generate($route, $parameters, $referenceType);
    }
}
