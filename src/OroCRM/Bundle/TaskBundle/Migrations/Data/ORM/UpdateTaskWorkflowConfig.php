<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class UpdateTaskWorkflowConfig extends AbstractFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $configManager  = $this->container->get('oro_entity_config.config_manager');
        $configProvider = $configManager->getProvider('workflow');

        $config = $configProvider->getConfig('OroCRM\Bundle\TaskBundle\Entity\Task');
        $config->set(
            'show_step_in_grid',
            false
        );
        $configManager->persist($config);
        $configManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
