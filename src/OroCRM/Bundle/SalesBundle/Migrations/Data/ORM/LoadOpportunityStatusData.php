<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\SalesBundle\Entity\OpportunityStatus;

class LoadOpportunityStatusData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $data = array(
        'in_progress' => 'In Progress',
        'won'         => 'Won',
        'lost'        => 'Lost',
    );

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $methodName => $methodLabel) {
            $method = new OpportunityStatus($methodName);
            $method->setLabel($methodLabel);
            $manager->persist($method);
        }

        $manager->flush();
    }
}
