<?php

namespace Oro\CRMCallBridgeBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;


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

        /**If CallBundle isn't installed do nothing**/
        if (!class_exists('OroCRM\Bundle\CallBundle\OroCRMCallBundle')) {
           return;
        }

        /** @var AclManager $aclManager */
        $aclManager = $this->container->get('oro_security.acl.manager');

        $fileName = $this->container
            ->get('kernel')
            ->locateResource('@OroCRMCallBridgeBundle/Migrations/Data/ORM/CrmRoles/roles.yml');

        $fileName  = str_replace('/', DIRECTORY_SEPARATOR, $fileName);
        $rolesData = Yaml::parse(file_get_contents($fileName));

        foreach ($rolesData as $roleName => $roleConfigData) {
            if (isset($roleConfigData['bap_role'])) {
                $role = $manager->getRepository('OroUserBundle:Role')
                    ->findOneBy(['role' => $roleConfigData['bap_role']]);
            } else {
                $role = new Role($roleName);
            }

            $role->setLabel($roleConfigData['label']);
            $manager->persist($role);

            if ($aclManager->isAclEnabled()) {
                $sid = $aclManager->getSid($role);
                foreach ($roleConfigData['permissions'] as $permission => $acls) {
                    $this->processPermission($aclManager, $sid, $permission, $acls);
                }
            }
        }

        $aclManager->flush();
        $manager->flush();
    }

    /**
     * @param AclManager $aclManager
     * @param mixed $sid
     * @param string $permission
     * @param array $acls
     */
    protected function processPermission(
        AclManager $aclManager,
        SecurityIdentityInterface $sid,
        $permission,
        array $acls
    ) {
        $oid = $aclManager->getOid(str_replace('|', ':', $permission));

        $extension = $aclManager->getExtensionSelector()->select($oid);
        $maskBuilders = $extension->getAllMaskBuilders();

        foreach ($maskBuilders as $maskBuilder) {
            $mask = $maskBuilder->reset()->get();

            if (!empty($acls)) {
                foreach ($acls as $acl) {
                    if ($maskBuilder->hasMask('MASK_' . $acl)) {
                        $mask = $maskBuilder->add($acl)->get();
                    }
                }
            }

            $aclManager->setPermission($sid, $oid, $mask);
        }
    }
}
