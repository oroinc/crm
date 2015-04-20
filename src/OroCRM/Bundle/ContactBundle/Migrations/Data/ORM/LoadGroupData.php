<?php

namespace OroCRM\Bundle\ContactBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\ContactBundle\Entity\Group;

class LoadGroupData extends AbstractFixture
{
    /**
     * Load sample groups
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $groups = array('Sales Group','Marketing Group');
        foreach ($groups as $group) {
            $contactGroup = new Group($group);
            $manager->persist($contactGroup);
        }
        $manager->flush();
    }
}
