<?php

namespace OroCRM\Bundle\TaskBundle\DataFixtures\ORM;

use OroCRM\Bundle\TaskBundle\Entity\Task;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints\DateTime;

class LoadTaskData extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $assignedTo = $reporter = $manager
            ->getRepository('OroUserBundle:User')
            ->findOneBy(
                array(
                    'username' => 'admin'
                )
            );

        if (!$reporter) {
            return;
        }

        $task = new Task();
        $task->setSubject('Acl task');
        $task->setDescription('New description');
        $task->setDueDate(new \DateTime());
        $task->setReporter($reporter);
        $task->setOwner($assignedTo);

        $manager->persist($task);
        $manager->flush();
    }
}
