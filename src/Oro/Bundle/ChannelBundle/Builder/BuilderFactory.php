<?php

namespace Oro\Bundle\ChannelBundle\Builder;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

/**
 * The factory to create a {@see ChannelObjectBuilder}.
 */
class BuilderFactory
{
    private ManagerRegistry $doctrine;
    private SettingsProvider $settingsProvider;

    public function __construct(ManagerRegistry $doctrine, SettingsProvider $settingsProvider)
    {
        $this->doctrine = $doctrine;
        $this->settingsProvider = $settingsProvider;
    }

    public function createBuilderForChannel(Channel $channel): ChannelObjectBuilder
    {
        return $this->createChannelObjectBuilder($channel);
    }

    public function createBuilderForIntegration(Integration $integration): ChannelObjectBuilder
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

        $builder = $this->createChannelObjectBuilder($channel);
        $builder
            ->setChannelType($channelType)
            ->setEntities($entities);

        return $builder;
    }

    public function createBuilder(): ChannelObjectBuilder
    {
        return $this->createChannelObjectBuilder(new Channel());
    }

    private function getChannelTypeForIntegration(string $integrationType): ?string
    {
        $channelTypes = $this->settingsProvider->getChannelTypes();
        foreach ($channelTypes as $channelType => $config) {
            if ($this->settingsProvider->getIntegrationType($channelType) === $integrationType) {
                return $channelType;
            }
        }

        return null;
    }

    private function createChannelObjectBuilder(Channel $channel): ChannelObjectBuilder
    {
        return new ChannelObjectBuilder($this->doctrine->getManager(), $this->settingsProvider, $channel);
    }
}
