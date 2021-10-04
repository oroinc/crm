<?php

namespace Oro\Bridge\CustomerPortalCRM\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\FrontendBundle\Migrations\Data\ORM\LoadUserRolesData;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractUpdatePermissions;

/**
 * Updates permissions for Account and Contact entities for ROLE_ACCOUNT_MANAGER role.
 */
class LoadAccountAndContactToAccountManagerRole extends AbstractUpdatePermissions implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadUserRolesData::class];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        if ($this->container->get(ApplicationState::class)->isInstalled()) {
            return;
        }

        $aclManager = $this->getAclManager();
        if (!$aclManager->isAclEnabled()) {
            return;
        }

        $role = $this->getRole($manager, 'ROLE_ACCOUNT_MANAGER');
        $this->setEntityPermissions(
            $aclManager,
            $role,
            Account::class,
            ['VIEW_SYSTEM', 'CREATE_SYSTEM', 'EDIT_SYSTEM', 'DELETE_SYSTEM', 'ASSIGN_SYSTEM']
        );
        $this->setEntityPermissions(
            $aclManager,
            $role,
            Contact::class,
            ['VIEW_SYSTEM', 'CREATE_SYSTEM', 'EDIT_SYSTEM', 'DELETE_SYSTEM', 'ASSIGN_SYSTEM']
        );
        $aclManager->flush();
    }
}
