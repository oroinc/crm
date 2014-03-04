<?php

namespace OroCRM\Bundle\TaskBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use OroCRM\Bundle\TaskBundle\Entity\TaskPriority;

class LoadTaskPriority extends AbstractFixture
{

    protected $data = array(
        array('title'=>'Low Priority', 'type' => 'low'),
        array('title'=>'Normal Priority', 'type' => 'normal'),
        array('title'=>'High Priority', 'type' => 'high')
    );

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $priority) {
            if (!$this->isPriorityExist($manager, $priority['type'])) {
                $entity = new TaskPriority();
                $entity->setType($priority['type']);
                $entity->setTitle($priority['title']);
                $manager->persist($entity);
            }
        }

        $manager->flush();
    }

    private function isPriorityExist(ObjectManager $manager, $priorityType)
    {
        return count(
            $manager->getRepository('OroCRMTaskBundle:TaskPriority')
                ->findBy(array('type' => $priorityType))
        ) > 0;
    }
}
