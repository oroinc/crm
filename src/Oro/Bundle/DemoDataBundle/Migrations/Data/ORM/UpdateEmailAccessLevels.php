<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractUpdatePermissions;

/**
 * Updates permissions for EmailUser entity for the following roles:
 * * ROLE_MARKETING_MANAGER
 * * ROLE_ONLINE_SALES_REP
 * * ROLE_LEADS_DEVELOPMENT_REP
 */
class UpdateEmailAccessLevels extends AbstractUpdatePermissions implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadRolesData::class];
    }

    public function load(ObjectManager $manager)
    {
        $aclManager = $this->getAclManager();
        if (!$aclManager->isAclEnabled()) {
            return;
        }

        $roleNames = [
            'ROLE_MARKETING_MANAGER',
            'ROLE_ONLINE_SALES_REP',
            'ROLE_LEADS_DEVELOPMENT_REP',
        ];
        $permissions = ['VIEW_BASIC', 'CREATE_BASIC', 'EDIT_BASIC'];
        foreach ($roleNames as $roleName) {
            $this->setEntityPermissions(
                $aclManager,
                $this->getRole($manager, $roleName),
                EmailUser::class,
                $permissions
            );
        }
        $aclManager->flush();
    }
}
