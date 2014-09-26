<?php

namespace OroCRM\Bundle\AccountBundle\Migrations\Data\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class UpdateEntityLabels extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager $manager
     * @throws \RuntimeException
     */
    public function load(ObjectManager $manager)
    {
        $configProvider = $this->container->get('oro_entity_config.provider.entity');

        $entityConfig = $configProvider->getConfig('OroCRM\Bundle\AccountBundle\Entity\Account');
        $entityConfig->set('label', 'orocrm.account.entity_label');
        $configProvider->persist($entityConfig);

        $configProvider->flush();
    }
}
