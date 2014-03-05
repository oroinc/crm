<?php

namespace OroCRM\Bundle\TaskBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\TaskBundle\Entity\TaskPriority;

class LoadTaskPriority extends AbstractFixture
{

    protected $data = array(
        array('label'=>'Low Priority', 'name' => 'low'),
        array('label'=>'Normal Priority', 'name' => 'normal'),
        array('label'=>'High Priority', 'name' => 'high')
    );

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $priority) {
            if (!$this->isPriorityExist($manager, $priority['name'])) {
                $entity = new TaskPriority($priority['name']);
                $entity->setLabel($priority['label']);
                $manager->persist($entity);
            }
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param string $priorityType
     * @return bool
     */
    private function isPriorityExist(ObjectManager $manager, $priorityType)
    {
        return count($manager->getRepository('OroCRMTaskBundle:TaskPriority')->find($priorityType)) > 0;
    }
}
