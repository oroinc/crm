<?php

namespace OroCRM\Bundle\IntegrationBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\IntegrationBundle\Entity\ChannelType;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadChannels extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $settings = [
            'wsdl_url' => 'http://localhost/api/v2_soap/?wsdl=1',
            'api_key'  => '123API1',
            'api_user' => 'api_user',
        ];

        $ch1 = new ChannelType();
        $ch1->setName('magento')
            ->setSettings(json_encode($settings));

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->persist($ch1);
        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 200;
    }
}
