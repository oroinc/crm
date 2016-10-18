<?php

namespace Oro\Bundle\ChannelBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;

class DefaultChannelData extends AbstractDefaultChannelDataFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $settingsProvider = $this->container->get('oro_channel.provider.settings_provider');

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
            $builder = $this->container->get('oro_channel.builder.factory')
                ->createBuilderForIntegration($integration);
            $builder->setOwner($integration->getOrganization());
            $builder->setDataSource($integration);
            $builder->setStatus($integration->isEnabled() ? Channel::STATUS_ACTIVE : Channel::STATUS_INACTIVE);
            $builder->setName($integration->getName() . ' channel');

            $channel = $builder->getChannel();
            $this->saveChannel($channel);

            foreach ($channel->getEntities() as $entity) {
                $this->fillChannelToEntity($channel, $entity, ['channel' => $integration]);
            }

            $this->updateLifetimeForAccounts($channel);
        }
    }

    /**
     * @param Channel $channel
     */
    protected function saveChannel(Channel $channel)
    {
        $this->em->persist($channel);
        $this->em->flush();
    }
}
