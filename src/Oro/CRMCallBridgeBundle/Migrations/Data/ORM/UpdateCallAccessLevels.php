<?php

namespace Oro\CRMCallBridgeBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

class UpdateCallAccessLevels extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\ORM\LoadRolesData'
        ];
    }

    /**
     * Update call access levels
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        if ($this->container->hasParameter('installed') && $this->container->getParameter('installed')) {
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
            if (!array_key_exists('bap_role', $roleConfigData)) {
                continue;
            }

            $role = $manager->getRepository('OroUserBundle:Role')
                            ->findOneBy(['role' => $roleConfigData['bap_role']]);

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
     * @param SecurityIdentityInterface $sid
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

            foreach ($acls as $acl) {
                if ($maskBuilder->hasMask('MASK_' . $acl)) {
                    $mask = $maskBuilder->add($acl)->get();
                }
            }

            $aclManager->setPermission($sid, $oid, $mask);
        }
    }
}
