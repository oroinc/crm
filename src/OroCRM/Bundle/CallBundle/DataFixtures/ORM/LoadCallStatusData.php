<?php

namespace OroCRM\Bundle\CallBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\CallBundle\Entity\CallStatus;

class LoadLeadStatusData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $data = array(
        'in_progress',
        'completed',
    );

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $status) {
            $callStatus= new CallStatus();
            $callStatus->setStatus($status);
            $manager->persist($callStatus);
        }

        $manager->flush();
    }
}
