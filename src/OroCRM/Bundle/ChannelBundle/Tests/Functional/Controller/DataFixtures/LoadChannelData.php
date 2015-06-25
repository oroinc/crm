<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Functional\Controller\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Builder\BuilderFactory;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class LoadChannelData extends AbstractFixture implements ContainerAwareInterface
{
    const DEFAULT_ORGANIZATION_ID = 1;

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $channels = [
        //Name           => status
        'Test Channel 1' => Channel::STATUS_ACTIVE,
        'Test Channel 2' => Channel::STATUS_INACTIVE,
        'Test Channel 3' => Channel::STATUS_ACTIVE,
    ];

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load roles
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Organization $organization */
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')
            ->find(self::DEFAULT_ORGANIZATION_ID);

        /** @var BuilderFactory $factory */
        $factory = $this->container->get('orocrm_channel.builder.factory');

        foreach ($this->channels as $name => $status) {
            $channel = $factory->createBuilder()
                ->setStatus($status)
                ->setChannelType('b2b')
                ->setName($name)
                ->setOwner($organization)
                ->getChannel();
            $manager->persist($channel);
        }
        $manager->flush();
    }
}
