<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\ChannelBundle\Entity\Channel;

class LoadChannels extends AbstractFixture
{
    /** @var ObjectManager */
    protected $em;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->em = $manager;
        $date = new \DateTime('now');

        $channel = new Channel();
        $channel
            ->setName('first channel')
            ->setStatus(true)
            ->setOwner($this->loadOwner())
            ->setChannelType('testType')
            ->setCreatedAt($date)
            ->setUpdatedAt($date)
            ->setCustomerIdentity('test1')
            ->setEntities(['test1', 'test2']);

        $manager->persist($channel);

        $this->setReference('channel_1', $channel);

        $channel2 = new Channel();
        $channel2
            ->setName('second channel')
            ->setOwner($this->loadOwner())
            ->setChannelType('testType')
            ->setCreatedAt($date)
            ->setUpdatedAt($date)
            ->setCustomerIdentity('test1')
            ->setEntities(['Oro\Bundle\ChannelBundle\Entity\CustomerIdentity']);

        $manager->persist($channel2);

        $this->setReference('channel_2', $channel2);

        $manager->flush();
    }

    /**
     * @return Organization|null
     */
    protected function loadOwner()
    {
        return $this->em->getRepository('OroOrganizationBundle:Organization')->getFirst();
    }
}
