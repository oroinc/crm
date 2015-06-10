<?php

namespace OroCRM\Bundle\ActivityContactBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use JMS\JobQueueBundle\Entity\Job;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use OroCRM\Bundle\ActivityContactBundle\Command\ActivityContactRecalculateCommand;

class ActivityContactRecalculate extends AbstractFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $job = new Job(ActivityContactRecalculateCommand::COMMAND_NAME, ['-v']);
        $em  = $this->container->get('doctrine')->getManager();
        $em->persist($job);
        $em->flush($job);
    }
}
