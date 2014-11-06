<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\MigrationBundle\Fixture\LoadedFixtureVersionAwareInterface;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Builder\BuilderFactory;
use OroCRM\Bundle\ChannelBundle\Migrations\Data\ORM\AbstractDefaultChannelDataFixture;

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
            $builderFactory = $this->container->get('orocrm_channel.builder.factory');
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

            $channels = $em->getRepository('OroCRMChannelBundle:Channel')
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
