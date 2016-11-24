<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

abstract class LoadAclRolesData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
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
            'Oro\Bundle\DemoDataBundle\Migrations\Data\ORM\LoadRolesData',
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
        /** @var AclManager $aclManager */
        $aclManager = $this->container->get('oro_security.acl.manager');

        if (!$aclManager->isAclEnabled()) {
            return;
        }

        $fileName = $this->container
            ->get('kernel')
            ->locateResource($this->getDataPath());

        $fileName  = str_replace('/', DIRECTORY_SEPARATOR, $fileName);
        $rolesData = Yaml::parse(file_get_contents($fileName));

        foreach ($rolesData as $roleName => $roleConfigData) {
            $role = $manager->getRepository('OroUserBundle:Role')->findOneBy(['role' => $roleName]);
            if (!$role) {
                continue;
            }
            $sid = $aclManager->getSid($role);
            foreach ($roleConfigData['permissions'] as $permission => $acls) {
                $this->processPermission($aclManager, $sid, $permission, $acls);
            }
        }

        $aclManager->flush();
    }

    /**
     * Gets path to load data from.
     *
     * @return string
     */
    abstract protected function getDataPath();

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
