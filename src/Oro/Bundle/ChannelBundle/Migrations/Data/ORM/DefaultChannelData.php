<?php

namespace Oro\Bundle\ChannelBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

/**
 * Loads default channels.
 */
class DefaultChannelData extends AbstractDefaultChannelDataFixture
{
    #[\Override]
    public function load(ObjectManager $manager): void
    {
        // create channels for integrations
        $types = $this->container->get('oro_channel.provider.settings_provider')->getSourceIntegrationTypes();

        if (empty($types)) {
            return;
        }

        $integrations = $manager->getRepository(Integration::class)->findBy(['type' => $types]);

        /** @var Integration $integration */
        foreach ($integrations as $integration) {
            $builder = $this->container->get('oro_channel.builder.factory')
                ->createBuilderForIntegration($integration);
            $builder->setOwner($integration->getOrganization());
            $builder->setDataSource($integration);
            $builder->setStatus($integration->isEnabled() ? Channel::STATUS_ACTIVE : Channel::STATUS_INACTIVE);
            $builder->setName($integration->getName() . ' channel');

            $channel = $builder->getChannel();
            $manager->persist($channel);
            $manager->flush();

            foreach ($channel->getEntities() as $entity) {
                $this->fillChannelToEntity($manager, $channel, $entity, ['channel' => $integration]);
            }

            $this->updateLifetimeForAccounts($manager, $channel);
        }
    }
}
