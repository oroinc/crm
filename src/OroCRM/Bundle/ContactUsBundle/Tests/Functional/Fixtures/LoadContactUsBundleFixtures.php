<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Functional\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\ChannelBundle\Builder\BuilderFactory;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadContactUsBundleFixtures extends AbstractFixture implements ContainerAwareInterface
{
    const CHANNEL_TYPE = 'custom';
    const CHANNEL_NAME = 'custom Channel';

    /** @var ObjectManager */
    protected $em;

    /** @var BuilderFactory */
    protected $factory;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->factory = $container->get('orocrm_channel.builder.factory');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->em = $manager;
        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();
        $this->createChannel();

        $contactUsRequest = new ContactRequest();
        $contactUsRequest->setFirstName('fname');
        $contactUsRequest->setLastName('lname');
        $contactUsRequest->setPhone('123123123');
        $contactUsRequest->setEmailAddress('email@email.com');
        $contactUsRequest->setComment('some comment');
        $contactUsRequest->setDataChannel($this->getReference('default_channel'));
        $contactUsRequest->setOwner($organization);

        $this->em->persist($contactUsRequest);
        $this->em->flush();

        $this->setReference('default_contact_us_request', $contactUsRequest);
    }

    /**
     * @return Channel
     */
    protected function createChannel()
    {
        $channel = $this
            ->factory
            ->createBuilder()
            ->setName(self::CHANNEL_NAME)
            ->setChannelType(self::CHANNEL_TYPE)
            ->setStatus(Channel::STATUS_ACTIVE)
            ->setOwner($this->em->getRepository('OroOrganizationBundle:Organization')->getFirst())
            ->getChannel();

        $this->em->persist($channel);
        $this->em->flush();

        $this->setReference('default_channel', $channel);
    }
}
