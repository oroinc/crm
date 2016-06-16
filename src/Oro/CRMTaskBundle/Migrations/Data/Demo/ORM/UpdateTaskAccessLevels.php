<?php

namespace Oro\CRMTaskBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\UserBundle\Entity\Role;

class UpdateTaskAccessLevels extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['OroCRM\Bundle\DemoDataBundle\Migrations\Data\ORM\LoadRolesData'];
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load ACL for security roles
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->objectManager = $manager;

        /** @var AclManager $aclManager */
        $aclManager = $this->container->get('oro_security.acl.manager');

        if ($aclManager->isAclEnabled()) {
            $this->updateUserRole($aclManager);
            $aclManager->flush();
        }
    }

    protected function updateUserRole(AclManager $manager)
    {
        $roles = [
            'ROLE_SALES_MANAGER',
            'ROLE_SALES_REP',
            'ROLE_ONLINE_SALES_REP',
            'ROLE_MARKETING_MANAGER',
            'ROLE_LEADS_DEVELOPMENT_REP',
        ];
        $acls = [
            'CREATE_SYSTEM',
            'VIEW_SYSTEM',
            'EDIT_SYSTEM',
            'DELETE_SYSTEM',
            'ASSIGN_SYSTEM'
        ];
        foreach ($roles as $roleName) {
            $role = $this->getRole($roleName);
            if ($role) {
                $sid = $manager->getSid($role);
                $oid = $manager->getOid('entity:OroCRM\Bundle\TaskBundle\Entity\Task');
                $extension = $manager->getExtensionSelector()->select($oid);
                $maskBuilders = $extension->getAllMaskBuilders();

                foreach ($maskBuilders as $maskBuilder) {
                    $mask = $maskBuilder->reset()->get();

                    foreach ($acls as $acl) {
                        if ($maskBuilder->hasMask('MASK_' . $acl)) {
                            $mask = $maskBuilder->add($acl)->get();
                        }
                    }

                    $manager->setPermission($sid, $oid, $mask);
                }
            }
        }
    }

    /**
     * @param string $roleName
     * @return Role|null
     */
    protected function getRole($roleName)
    {
        return $this->objectManager->getRepository('OroUserBundle:Role')->findOneBy(['role' => $roleName]);
    }
}
