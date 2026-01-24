<?php

namespace Oro\Bundle\ContactBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Group;

/**
 * Loads predefined contact group data during database initialization.
 */
class LoadGroupData extends AbstractFixture
{
    /**
     * Load sample groups
     */
    #[\Override]
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
