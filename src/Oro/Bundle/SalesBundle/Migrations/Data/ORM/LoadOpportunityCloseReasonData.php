<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\SalesBundle\Entity\OpportunityCloseReason;

/**
 * Loads predefined opportunity close reason data during database initialization.
 */
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

    #[\Override]
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
