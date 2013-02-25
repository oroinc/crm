<?php

namespace Oro\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\UserBundle\Entity\Role;

class LoadRolesData extends AbstractFixture
{
    /**
     * Load roles
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $roleAnonymous = new Role('IS_AUTHENTICATED_ANONYMOUSLY');
        $roleAnonymous->setLabel('Anonymous');

        $roleUser = new Role('ROLE_USER');
        $roleUser->setLabel('User');

        $manager->persist($roleAnonymous);
        $manager->persist($roleUser);

        $manager->flush();
    }
}
