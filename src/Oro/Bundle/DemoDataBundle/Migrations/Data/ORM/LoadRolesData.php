<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractLoadAclData;
use Oro\Bundle\UserBundle\Entity\Role;

class LoadRolesData extends AbstractLoadAclData
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData',
            'Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDataPath()
    {
        return '@OroDemoDataBundle/Migrations/Data/ORM/CrmRoles/roles.yml';
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        parent::load($manager);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRole(ObjectManager $objectManager, $roleName, $roleConfigData)
    {
        $role = parent::getRole($objectManager, $roleName, $roleConfigData);
        if (null === $role) {
            $role = new Role($roleName);
        }

        return $role;
    }
}
