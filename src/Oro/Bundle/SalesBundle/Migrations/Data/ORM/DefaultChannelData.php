<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Builder\BuilderFactory;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Migrations\Data\ORM\AbstractDefaultChannelDataFixture;
use Oro\Bundle\MigrationBundle\Fixture\LoadedFixtureVersionAwareInterface;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

/**
 * Loads "b2b" default channel.
 */
class DefaultChannelData extends AbstractDefaultChannelDataFixture implements
    VersionedFixtureInterface,
    LoadedFixtureVersionAwareInterface
{
    public const B2B_CHANNEL_TYPE = 'b2b';

    private ?string $alreadyLoadedVersion = null;

    #[\Override]
    public function getVersion(): string
    {
        return '1.0';
    }

    #[\Override]
    public function setLoadedVersion($version = null): void
    {
        $this->alreadyLoadedVersion = $version;
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        if (!$this->alreadyLoadedVersion) {
            /** @var BuilderFactory $builderFactory */
            $builderFactory = $this->container->get('oro_channel.builder.factory');
            $channel = $builderFactory->createBuilder()
                ->setChannelType(self::B2B_CHANNEL_TYPE)
                ->setStatus(Channel::STATUS_ACTIVE)
                ->setEntities()
                ->getChannel();

            $entities = $channel->getEntities();

            $shouldBeCreated = false;
            foreach ($entities as $entity) {
                $shouldBeCreated |= $this->getRowCount($manager, $entity);
                if ($shouldBeCreated) {
                    break;
                }
            }

            if ($shouldBeCreated) {
                $manager->persist($channel);
                $manager->flush();

                // fill channel to all existing entities
                foreach ($entities as $entity) {
                    $this->fillChannelToEntity($manager, $channel, $entity);
                }

                $this->updateLifetimeForAccounts($manager, $channel);
            }
        } elseif ('0.0' === $this->alreadyLoadedVersion) {
            $channels = $manager->getRepository(Channel::class)
                ->findBy(['channelType' => self::B2B_CHANNEL_TYPE]);
            foreach ($channels as $channel) {
                $this->updateLifetimeForAccounts($manager, $channel);
            }
        }
    }
}
