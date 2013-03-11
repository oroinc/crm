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
        $this->addReference('anon_role', $roleAnonymous);

        $roleUser = new Role('ROLE_USER');
        $roleUser->setLabel('User');
        $this->addReference('user_role', $roleUser);

        $roleAdmin = new Role('ROLE_SUPER_ADMIN');
        $roleAdmin->setLabel('Super admin');
        $this->addReference('admin_role', $roleAdmin);

        $manager->persist($roleAnonymous);
        $manager->persist($roleUser);
        $manager->persist($roleAdmin);

        $manager->flush();
    }
}
