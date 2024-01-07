<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class LoadChannel extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadOrganization::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $date = new \DateTime('now');

        $channel = new Channel();
        $channel->setName('some name');
        $channel->setOwner($this->getReference(LoadOrganization::ORGANIZATION));
        $channel->setChannelType('testType');
        $channel->setCreatedAt($date);
        $channel->setUpdatedAt($date);
        $channel->setCustomerIdentity('test1');
        $channel->setEntities(['test1', 'test2']);
        $manager->persist($channel);
        $this->setReference('default_channel', $channel);

        $manager->flush();
    }
}
