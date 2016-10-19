<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Builder\BuilderFactory;
use Oro\Bundle\SalesBundle\Migrations\Data\ORM\DefaultChannelData;

class LoadChannelData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /** @var BuilderFactory */
    protected $factory;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadBusinessUnitData'];
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->factory = $container->get('oro_channel.builder.factory');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $om)
    {
        $channel = $this
            ->factory
            ->createBuilder()
            ->setStatus(Channel::STATUS_ACTIVE)
            ->setEntities()
            ->setChannelType(DefaultChannelData::B2B_CHANNEL_TYPE)
            ->setName('Sales channel')
            ->getChannel();

        $om->persist($channel);
        $om->flush();

        $this->addReference('default_channel', $channel);
    }
}
