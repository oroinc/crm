<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Builder\BuilderFactory;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\SalesBundle\Migrations\Data\ORM\DefaultChannelData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Load default "b2b" channel.
 */
class LoadChannelData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadBusinessUnitData::class];
    }

    #[\Override]
    public function load(ObjectManager $manager)
    {
        /** @var BuilderFactory $factory */
        $factory = $this->container->get('oro_channel.builder.factory');
        $channel = $factory
            ->createBuilder()
            ->setStatus(Channel::STATUS_ACTIVE)
            ->setEntities()
            ->setChannelType(DefaultChannelData::B2B_CHANNEL_TYPE)
            ->setName('Sales channel')
            ->getChannel();

        $manager->persist($channel);
        $manager->flush();

        $this->addReference('default_channel', $channel);
    }
}
