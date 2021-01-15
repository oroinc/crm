<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Builder\BuilderFactory;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Migrations\Data\ORM\AbstractDefaultChannelDataFixture;
use Oro\Bundle\MigrationBundle\Fixture\LoadedFixtureVersionAwareInterface;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

class DefaultChannelData extends AbstractDefaultChannelDataFixture implements
    VersionedFixtureInterface,
    LoadedFixtureVersionAwareInterface
{
    /** @var string */
    private $version;

    const B2B_CHANNEL_TYPE = 'b2b';

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '1.0';
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        if (!$this->version) {
            /** @var BuilderFactory $builderFactory */
            $builderFactory = $this->container->get('oro_channel.builder.factory');
            $channel        = $builderFactory
                ->createBuilder()
                ->setChannelType(self::B2B_CHANNEL_TYPE)
                ->setStatus(Channel::STATUS_ACTIVE)
                ->setEntities()
                ->getChannel();

            $entities = $channel->getEntities();

            $shouldBeCreated = false;
            foreach ($entities as $entity) {
                $shouldBeCreated |= $this->getRowCount($entity);

                if ($shouldBeCreated) {
                    break;
                }
            }

            if ($shouldBeCreated) {
                $this->em->persist($channel);
                $this->em->flush();

                // fill channel to all existing entities
                foreach ($entities as $entity) {
                    $this->fillChannelToEntity($channel, $entity);
                }

                $this->updateLifetimeForAccounts($channel);
            }
        } elseif ('0.0' === $this->version) {
            $em = $this->container->get('doctrine')->getManager();

            $channels = $em->getRepository('OroChannelBundle:Channel')
                ->findBy(['channelType' => self::B2B_CHANNEL_TYPE]);

            foreach ($channels as $channel) {
                $this->updateLifetimeForAccounts($channel);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setLoadedVersion($version = null)
    {
        $this->version = $version;
    }
}
