<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\ORM;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractLoadAclData;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData as LoadUserRolesData;

/**
 * Sets permissions defined in "@OroDemoDataBundle/Migrations/Data/ORM/CrmRoles/roles.yml" file.
 */
class LoadRolesData extends AbstractLoadAclData
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadOrganizationAndBusinessUnitData::class,
            LoadUserRolesData::class
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
    protected function getRole(ObjectManager $objectManager, $roleName, $roleConfigData)
    {
        $role = parent::getRole($objectManager, $roleName, $roleConfigData);
        if (null === $role) {
            $role = new Role($roleName);
        }

        return $role;
    }
}
