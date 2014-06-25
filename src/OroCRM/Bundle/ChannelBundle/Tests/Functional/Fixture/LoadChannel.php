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

        $channel = new Channel();
        $channel->setName('some name');
        $channel->setDescription('some description');
        $channel->setOwner($this->loadOwner());

        $manager->persist($channel);
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
