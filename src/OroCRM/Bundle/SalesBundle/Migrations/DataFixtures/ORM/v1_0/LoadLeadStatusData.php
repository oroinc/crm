<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\DataFixtures\ORM\v1_0;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\SalesBundle\Entity\LeadStatus;

class LoadLeadStatusData extends AbstractFixture
{
    /**
     * @var array
     */
    protected $data = array(
        'new'       => 'New',
        'qualified' => 'Qualified',
        'canceled'  => 'Canceled',
    );

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $methodName => $methodLabel) {
            $method = new LeadStatus($methodName);
            $method->setLabel($methodLabel);
            $manager->persist($method);
        }

        $manager->flush();
    }
}
