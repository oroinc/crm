<?php

namespace Oro\Bridge\CallCRM\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DemoDataBundle\Migrations\Data\ORM\LoadRolesData;
use Oro\Bundle\DistributionBundle\Handler\ApplicationState;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractUpdatePermissions;
use Symfony\Component\Yaml\Yaml;

/**
 * Sets permissions defined in "@OroCallCRMBridgeBundle/Migrations/Data/ORM/CrmRoles/roles.yml" file.
 */
class UpdateCallAccessLevels extends AbstractUpdatePermissions implements DependentFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [LoadRolesData::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        if ($this->container->get(ApplicationState::class)->isInstalled()) {
            return;
        }

        $aclManager = $this->getAclManager();
        if (!$aclManager->isAclEnabled()) {
            return;
        }

        $fileName = $this->container
            ->get('kernel')
            ->locateResource('@OroCallCRMBridgeBundle/Migrations/Data/ORM/CrmRoles/roles.yml');
        $fileName = str_replace('/', DIRECTORY_SEPARATOR, $fileName);
        $rolesData = Yaml::parse(file_get_contents($fileName));
        foreach ($rolesData as $roleName => $roleConfigData) {
            $role = $this->getRole($manager, $roleName);
            if (null !== $role) {
                foreach ($roleConfigData['permissions'] as $oid => $permissions) {
                    $this->replacePermissions(
                        $aclManager,
                        $role,
                        $aclManager->getOid(str_replace('|', ':', $oid)),
                        $permissions
                    );
                }
            }
        }
        $aclManager->flush();
    }
}
