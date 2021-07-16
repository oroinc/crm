<?php

namespace Oro\Bundle\ContactBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Group;

class LoadGroupData extends AbstractFixture
{
    /**
     * Load sample groups
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
