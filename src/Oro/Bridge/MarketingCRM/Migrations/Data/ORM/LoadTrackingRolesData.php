<?php

namespace Oro\Bridge\MarketingCRM\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\MigrationBundle\Fixture\RenamedFixtureInterface;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\SecurityBundle\Migrations\Data\ORM\AbstractUpdatePermissions;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData;
use Symfony\Component\Yaml\Yaml;

/**
 * Sets permissions defined in "@OroMarketingCRMBridgeBundle/Migrations/Data/ORM/CrmRoles/roles.yml" file.
 */
class LoadTrackingRolesData extends AbstractUpdatePermissions implements
    DependentFixtureInterface,
    RenamedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadOrganizationAndBusinessUnitData::class,
            LoadRolesData::class
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getPreviousClassNames(): array
    {
        return [
            'Oro\\Bridge\\MarketingCRM\\Migrations\\Migrations\\Data\\ORM\\LoadTrackingRolesData',
        ];
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $aclManager = $this->getAclManager();

        $fileName = $this->container
            ->get('kernel')
            ->locateResource('@OroMarketingCRMBridgeBundle/Migrations/Data/ORM/CrmRoles/roles.yml');
        $fileName  = str_replace('/', DIRECTORY_SEPARATOR, $fileName);
        $rolesData = Yaml::parse(file_get_contents($fileName));

        foreach ($rolesData as $roleName => $roleConfigData) {
            if (isset($roleConfigData['bap_role'])) {
                $role = $this->getRole($manager, $roleConfigData['bap_role']);
            } else {
                $role = new Role($roleName);
                $role->setLabel($roleConfigData['label']);
                $manager->persist($role);
            }

            if (null !== $role && $aclManager->isAclEnabled()) {
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
        $manager->flush();
        if ($aclManager->isAclEnabled()) {
            $aclManager->flush();
        }
    }
}
