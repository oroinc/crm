<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\Rest;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Builder\BuilderFactory;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\MagentoBundle\Entity\MagentoRestTransport;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Config\Common\ConfigObject;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadMagentoRestChannel extends AbstractFixture implements ContainerAwareInterface
{
    const CHANNEL_NAME = 'Magento REST channel';
    const CHANNEL_TYPE = 'magento2';

    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var MagentoTransport
     */
    protected $transport;

    /**
     * @var Integration
     */
    protected $integration;

    /**
     * @var Organization
     */
    protected $organization;

    /**
     * @var BuilderFactory
     */
    protected $factory;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->factory = $container->get('oro_channel.builder.factory');
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->em = $manager;
        $this->organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        $this->createTransport()
            ->createIntegration()
            ->createChannel();
    }

    public function createTransport()
    {
        $transport = new MagentoRestTransport();
        $transport->setAdminUrl('http://localhost/magento/admin');
        $transport->setApiKey('key');
        $transport->setApiUser('user');
        $transport->setIsExtensionInstalled(true);
        $transport->setExtensionVersion('2.1');
        $transport->setMagentoVersion('2.1.0');
        $transport->setApiUrl('http://localhost');
        $this->em->persist($transport);
        $this->em->flush();

        $this->transport = $transport;
        $this->addReference('default_transport', $transport);

        return $this;
    }

    public function createIntegration()
    {
        $integration = new Integration();
        $integration->setName('Demo Web store');
        $integration->setType(self::CHANNEL_TYPE);
        $integration->setConnectors(['customer', 'order', 'cart']);
        $integration->setTransport($this->transport);
        $integration->setOrganization($this->organization);

        $synchronizationSettings = ConfigObject::create(['isTwoWaySyncEnabled' => true]);
        $integration->setSynchronizationSettings($synchronizationSettings);

        $this->em->persist($integration);
        $this->em->flush();

        $this->setReference('default_integration_channel', $integration);
        $this->integration = $integration;

        return $this;
    }

    public function createChannel()
    {
        $channel = $this->factory
            ->createBuilderForIntegration($this->integration)
            ->setName(self::CHANNEL_NAME)
            ->setChannelType(self::CHANNEL_TYPE)
            ->setStatus(Channel::STATUS_ACTIVE)
            ->setOwner($this->organization)
            ->setEntities()
            ->getChannel();

        $this->em->persist($channel);
        $this->em->flush();

        $this->setReference('default_channel', $channel);

        return $this;
    }
}
