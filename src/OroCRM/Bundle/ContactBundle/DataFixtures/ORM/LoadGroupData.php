<?php

namespace OroCRM\Bundle\ContactBundle\DataFixtures\ORM;

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
        $manager->persist(new Group('Administrators'));
        $manager->persist(new Group('Sales'));
        $manager->persist(new Group('Marketing'));

        $manager->flush();
    }
}
