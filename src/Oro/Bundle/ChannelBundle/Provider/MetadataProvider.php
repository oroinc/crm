<?php

namespace Oro\Bundle\ChannelBundle\Provider;

use Oro\Bundle\EntityBundle\Provider\EntityProvider;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Symfony\Component\Routing\RouterInterface;

/**
 * Provides channel's metadata.
 */
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

        foreach ($this->getChannelEntities() as $entityName) {
            $entityConfig        = $this->entityProvider->getEntity($entityName, true);
            $extendConfig        = $this->configManager->getProvider('extend')->getConfig($entityName);
            $entityConfigModelId = $this->configManager->getConfigModelId($entityName);

            if (null !== $entityConfigModelId) {
                $entityConfig = array_merge($entityConfig, $this->getEntityLinks($entityConfigModelId));
            }

            $result[$entityName] = array_merge($entityConfig, ['type' => $extendConfig->get('owner')]);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannelTypeMetadata()
    {
        return $this->settings->getChannelTypeChoiceList();
    }

    /**
     * @return array
     */
    protected function getChannelEntities()
    {
        $customEntities = $this->configManager->getProvider('extend')->map(
            function (ConfigInterface $extendConfig) {
                $isCustom
                    = $extendConfig->is('is_extend')
                    && $extendConfig->get('owner') === ExtendScope::OWNER_CUSTOM
                    && $extendConfig->in('state', [ExtendScope::STATE_ACTIVE, ExtendScope::STATE_UPDATE]);

                return $isCustom ? $extendConfig->getId()->getClassName() : false;
            }
        );
        $customEntities = array_filter($customEntities);

        $entities = array_map(
            function ($setting) {
                return $setting['name'];
            },
            $this->settings->getEntities()
        );

        return array_unique(array_merge($customEntities, $entities));
    }

    /**
     * @param int $entityConfigModelId
     *
     * @return array
     */
    protected function getEntityLinks($entityConfigModelId)
    {
        return [
            'entity_id' => $entityConfigModelId,
            'edit_link' => $this->generateUrl(self::ENTITY_EDIT_ROUTE, ['id' => $entityConfigModelId]),
            'view_link' => $this->generateUrl(self::ENTITY_VIEW_ROUTE, ['id' => $entityConfigModelId]),
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
