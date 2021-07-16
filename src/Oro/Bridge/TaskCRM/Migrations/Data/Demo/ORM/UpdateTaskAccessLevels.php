<?php

namespace Oro\Bridge\TaskCRM\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DemoDataBundle\Migrations\Data\ORM\LoadRolesData;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractUpdatePermissions;
use Oro\Bundle\TaskBundle\Entity\Task;

/**
 * Updates permissions for Task entity for the following roles:
 * * ROLE_SALES_MANAGER
 * * ROLE_SALES_REP
 * * ROLE_ONLINE_SALES_REP
 * * ROLE_MARKETING_MANAGER
 * * ROLE_LEADS_DEVELOPMENT_REP
 */
class UpdateTaskAccessLevels extends AbstractUpdatePermissions implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadRolesData::class];
    }

    /**
     * Load ACL for security roles
     */
    public function load(ObjectManager $manager)
    {
        $aclManager = $this->getAclManager();
        if (!$aclManager->isAclEnabled()) {
            return;
        }

        $roleNames = [
            'ROLE_SALES_MANAGER',
            'ROLE_SALES_REP',
            'ROLE_ONLINE_SALES_REP',
            'ROLE_MARKETING_MANAGER',
            'ROLE_LEADS_DEVELOPMENT_REP',
        ];
        $permissions = ['CREATE_SYSTEM', 'VIEW_SYSTEM', 'EDIT_SYSTEM', 'DELETE_SYSTEM', 'ASSIGN_SYSTEM'];
        foreach ($roleNames as $roleName) {
            $this->setEntityPermissions(
                $aclManager,
                $this->getRole($manager, $roleName),
                Task::class,
                $permissions
            );
        }
        $aclManager->flush();
    }
}
