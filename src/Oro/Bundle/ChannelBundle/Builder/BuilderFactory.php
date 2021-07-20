<?php

namespace Oro\Bundle\ChannelBundle\Builder;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * The factory to create ChannelObjectBuilder.
 */
class BuilderFactory
{
    /** @var ManagerRegistry */
    private $registry;

    /** @var SettingsProvider */
    private $settingsProvider;

    public function __construct(ManagerRegistry $registry, SettingsProvider $settingsProvider)
    {
        $this->registry = $registry;
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
        $channelType = $this->getChannelTypeForIntegration($integration->getType());

        $connectors = $integration->getConnectors();
        $entities = [];
        if ($channelType) {
            foreach ($this->settingsProvider->getEntitiesByChannelType($channelType) as $entityName) {
                $connector = $this->settingsProvider->getIntegrationConnectorName($entityName);
                $key = array_search($connector, $connectors);
                if (false !== $key) {
                    unset($connectors[$key]);
                    $entities[] = $entityName;
                }
            }
        }

        $connectors = array_diff($integration->getConnectors(), $connectors);

        // disable connectors without correspondent entity
        if ($channelType) {
            $identity = $this->settingsProvider->getCustomerIdentityFromConfig($channelType);
            if (!in_array($identity, $entities, true)) {
                array_unshift($entities, $identity);
                $connector = $this->settingsProvider->getIntegrationConnectorName($identity);
                if ($connector) {
                    array_unshift($connectors, $connector);
                }
            }
        }

        $integration->setEditMode(Integration::EDIT_MODE_DISALLOW);
        $integration->setConnectors($connectors);

        $builder = new ChannelObjectBuilder($this->registry->getManager(), $this->settingsProvider, $channel);
        $builder
            ->setChannelType($channelType)
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
     * @param string $integrationType
     *
     * @return string|null
     */
    private function getChannelTypeForIntegration($integrationType)
    {
        $channelTypes = $this->settingsProvider->getChannelTypes();
        foreach ($channelTypes as $channelType => $config) {
            if ($this->settingsProvider->getIntegrationType($channelType) === $integrationType) {
                return $channelType;
            }
        }

        return null;
    }
}
