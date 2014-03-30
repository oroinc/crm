<?php

namespace OroCRM\Bundle\CallBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\CallBundle\Entity\CallStatus;

class LoadCallStatusData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $data = array(
        'in_progress' => 'In progress',
        'completed' => 'Completed',
    );

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $name => $label) {
            $callStatus = new CallStatus($name);
            $callStatus->setLabel($label);
            $manager->persist($callStatus);
        }

        $manager->flush();
    }
}
