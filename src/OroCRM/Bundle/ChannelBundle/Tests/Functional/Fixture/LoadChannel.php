<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

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
        $channel->setName('some name');
        $channel->setOwner($this->loadOwner());
        $channel->setChannelType('testType');
        $channel->setCreatedAt($date);
        $channel->setUpdatedAt($date);
        $channel->setCustomerIdentity('test1');
        $channel->setEntities(['test1', 'test2']);

        $manager->persist($channel);

        $this->setReference('default_channel', $channel);

        $manager->flush();
    }

    /**
     * @return Organization|null
     */
    protected function loadOwner()
    {
        return $this->em->getRepository('OroOrganizationBundle:Organization')->findOneByName('default');
    }
}
