<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;

class LoadRolesData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

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
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load roles
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var AclManager $manager */
        $aclManager = $this->container->get('oro_security.acl.manager');

        $defaultBusinessUnit = $manager
            ->getRepository('OroOrganizationBundle:BusinessUnit')
            ->findOneBy(['name' => LoadOrganizationAndBusinessUnitData::MAIN_BUSINESS_UNIT]);

        if (!$defaultBusinessUnit) {
            $defaultBusinessUnit = $manager
                ->getRepository('OroOrganizationBundle:BusinessUnit')
                ->findOneBy(['name' => 'Acme, General']);
        }

        $fileName  = __DIR__ . '/CrmRoles/roles.yml';
        $fileName  = str_replace('/', DIRECTORY_SEPARATOR, $fileName);
        $rolesData = Yaml::parse($fileName);

        foreach ($rolesData as $roleName => $roleConfigData) {
            if (isset($roleConfigData['bap_role'])) {
                $role = $manager->getRepository('OroUserBundle:Role')
                    ->findOneBy(['role' => $roleConfigData['bap_role']]);
            } else {
                $role = new Role($roleName);
            }

            $role->setLabel($roleConfigData['label']);
            if ($defaultBusinessUnit) {
                $role->setOwner($defaultBusinessUnit);
            }
            $manager->persist($role);

            if ($aclManager->isAclEnabled()) {
                $sid = $aclManager->getSid($role);
                foreach ($roleConfigData['permissions'] as $permission => $acls) {
                    $oid     = $aclManager->getOid(str_replace('|', ':', $permission));
                    $builder = $aclManager->getMaskBuilder($oid);
                    $mask    = $builder->reset()->get();
                    if (!empty($acls)) {
                        foreach ($acls as $acl) {
                            $mask = $builder->add($acl)->get();
                        }
                    }
                    $aclManager->setPermission($sid, $oid, $mask);
                }
            }
        }

        $aclManager->flush();
        $manager->flush();
    }
}
