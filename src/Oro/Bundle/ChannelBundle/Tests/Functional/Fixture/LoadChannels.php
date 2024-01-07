<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\CustomerIdentity;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class LoadChannels extends AbstractFixture implements DependentFixtureInterface
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

        $channel1 = new Channel();
        $channel1->setName('first channel');
        $channel1->setStatus(true);
        $channel1->setOwner($this->getReference(LoadOrganization::ORGANIZATION));
        $channel1->setChannelType('testType');
        $channel1->setCreatedAt($date);
        $channel1->setUpdatedAt($date);
        $channel1->setCustomerIdentity('test1');
        $channel1->setEntities(['test1', 'test2']);
        $manager->persist($channel1);
        $this->setReference('channel_1', $channel1);

        $channel2 = new Channel();
        $channel2->setName('second channel');
        $channel2->setOwner($this->getReference(LoadOrganization::ORGANIZATION));
        $channel2->setChannelType('testType');
        $channel2->setCreatedAt($date);
        $channel2->setUpdatedAt($date);
        $channel2->setCustomerIdentity('test1');
        $channel2->setEntities([CustomerIdentity::class]);
        $manager->persist($channel2);
        $this->setReference('channel_2', $channel2);

        $manager->flush();
    }
}
