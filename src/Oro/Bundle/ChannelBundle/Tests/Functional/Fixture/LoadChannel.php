<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class LoadChannel extends AbstractFixture
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
            ->setName('some name')
            ->setOwner($this->loadOwner())
            ->setChannelType('testType')
            ->setCreatedAt($date)
            ->setUpdatedAt($date)
            ->setCustomerIdentity('test1')
            ->setEntities(['test1', 'test2']);

        $manager->persist($channel);

        $this->setReference('default_channel', $channel);

        $manager->flush();
    }

    /**
     * @return Organization|null
     */
    protected function loadOwner()
    {
        return $this->em->getRepository(Organization::class)->getFirst();
    }
}
