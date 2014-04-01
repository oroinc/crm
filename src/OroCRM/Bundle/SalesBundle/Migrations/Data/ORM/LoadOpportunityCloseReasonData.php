<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\SalesBundle\Entity\OpportunityCloseReason;

class LoadOpportunityCloseReasonData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $data = array(
        'outsold'   => 'Outsold',
        'won'       => 'Won',
        'cancelled' => 'Cancelled',
    );

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $methodName => $methodLabel) {
            $method = new OpportunityCloseReason($methodName);
            $method->setLabel($methodLabel);
            $manager->persist($method);
        }

        $manager->flush();
    }
}
