<?php

namespace Oro\Bundle\ChannelBundle\Builder;

use Symfony\Bridge\Doctrine\ManagerRegistry;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;

class BuilderFactory
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var SettingsProvider */
    protected $settingsProvider;

    /**
     * @param ManagerRegistry  $registry
     * @param SettingsProvider $settingsProvider
     */
    public function __construct(ManagerRegistry $registry, SettingsProvider $settingsProvider)
    {
        $this->registry         = $registry;
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * @param Channel $channel
     *
     * @return ChannelObjectBuilder
     */
    public function createBuilderForChannel(Channel $channel)
    {
        return new ChannelObjectBuilder($this->registry->getManager(), $this->settingsProvider, $channel);
    }

    /**
     * @param Integration $integration
     *
     * @return ChannelObjectBuilder
     */
    public function createBuilderForIntegration(Integration $integration)
    {
        $channel = new Channel();

        $settingsProvider = $this->settingsProvider;
        $type             = $this->getChannelTypeForIntegration($this->settingsProvider, $integration->getType());
        $connectors       = $integration->getConnectors();
        $entities         = array_filter(
            $settingsProvider->getEntitiesByChannelType($type),
            function ($entityName) use ($settingsProvider, &$connectors) {
                $connector = $settingsProvider->getIntegrationConnectorName($entityName);
                $key       = array_search($connector, $connectors);
                $enabled   = $key !== false;

                if ($enabled) {
                    unset($connectors[$key]);
                }

                return $enabled;
            }
        );

        // disable connectors without correspondent entity
        $connectors = array_diff($integration->getConnectors(), $connectors);
        $identity   = $settingsProvider->getCustomerIdentityFromConfig($type);
        if (!in_array($identity, $entities, true)) {
            array_unshift($entities, $identity);
            $connector = $settingsProvider->getIntegrationConnectorName($identity);
            if (false !== $connector) {
                array_unshift($connectors, $connector);
            }
        }

        $integration->setEditMode(Integration::EDIT_MODE_DISALLOW);
        $integration->setConnectors($connectors);

        $builder = new ChannelObjectBuilder($this->registry->getManager(), $this->settingsProvider, $channel);
        $builder
            ->setChannelType($type)
            ->setEntities($entities);

        return $builder;
    }

    /**
     * @return ChannelObjectBuilder
     */
    public function createBuilder()
    {
        return new ChannelObjectBuilder($this->registry->getManager(), $this->settingsProvider, new Channel());
    }

    /**
     * @param SettingsProvider $settingsProvider
     * @param string           $integrationType
     *
     * @return bool|string
     */
    protected function getChannelTypeForIntegration(SettingsProvider $settingsProvider, $integrationType)
    {
        $channelTypeConfigs = $settingsProvider->getSettings(SettingsProvider::CHANNEL_TYPE_PATH);

        foreach ($channelTypeConfigs as $channelTypeName => $config) {
            if ($settingsProvider->getIntegrationType($channelTypeName) == $integrationType) {
                return $channelTypeName;
            }
        }

        return false;
    }
}
