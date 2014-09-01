<?php

namespace OroCRM\Bundle\ChannelBundle\Migrations\Data\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class DefaultChannelData extends AbstractDefaultChannelDataFixture implements ContainerAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $settingsProvider = $this->container->get('orocrm_channel.provider.settings_provider');

        $this->createChannelsForIntegrations($settingsProvider);
    }

    /**
     * @param SettingsProvider $settingsProvider
     */
    protected function createChannelsForIntegrations(SettingsProvider $settingsProvider)
    {
        // create channels for integrations
        $types        = $settingsProvider->getSourceIntegrationTypes();
        $integrations = $this->em->getRepository('OroIntegrationBundle:Channel')
            ->findBy(['type' => $types]);

        /** @var Integration $integration */
        foreach ($integrations as $integration) {
            $type = $this->getChannelTypeForIntegration($settingsProvider, $integration->getType());

            $connectors = $integration->getConnectors();
            $entities   = array_filter(
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
            $owner      = $integration->getOrganization();
            if (!$owner) {
                $owner = $this->em->getRepository('OroOrganizationBundle:Organization')
                    ->createQueryBuilder('o')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getSingleResult();
            }

            // ensure identity included
            $identity = $settingsProvider->getCustomerIdentityFromConfig($type);
            if (!in_array($identity, $entities, true)) {
                array_unshift($entities, $identity);
                $connector = $settingsProvider->getIntegrationConnectorName($identity);
                if (false !== $connector) {
                    array_unshift($connectors, $connector);
                }
            }

            $channel = new Channel();
            $channel->setName($integration->getName() . ' channel');
            $channel->setChannelType($type);
            $channel->setStatus($integration->getEnabled() ? Channel::STATUS_ACTIVE : Channel::STATUS_INACTIVE);
            $channel->setEntities($entities);
            $channel->setCustomerIdentity($identity);
            $channel->setDataSource($integration);
            $channel->setOwner($owner);

            $integration->setEditMode(Integration::EDIT_MODE_DISALLOW);
            $integration->setConnectors($connectors);

            $this->em->persist($channel);
            $this->em->flush();

            $this->fillChannelToEntity($channel, $entities);
        }
    }

    /**
     * @param SettingsProvider $settingsProvider
     * @param  string          $integrationType
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
