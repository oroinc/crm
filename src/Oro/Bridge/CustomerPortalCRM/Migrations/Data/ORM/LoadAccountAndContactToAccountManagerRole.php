<?php

namespace Oro\Bridge\CustomerPortalCRM\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface as SID;

/**
 * Adds permissions for Account and Contact CRM entities to ROLE_ACCOUNT_MANAGER role.
 */
class LoadAccountAndContactToAccountManagerRole extends AbstractFixture implements
    DependentFixtureInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;

    const ACCOUNT_MANAGER_ROLE = 'ROLE_ACCOUNT_MANAGER';

    const ACCOUNT_ENTITY = 'Oro\Bundle\AccountBundle\Entity\Account';
    const CONTACT_ENTITY = 'Oro\Bundle\ContactBundle\Entity\Contact';

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\FrontendBundle\Migrations\Data\ORM\LoadUserRolesData'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        if ($this->container->hasParameter('installed') && $this->container->getParameter('installed')) {
            return;
        }

        /** @var AclManager $aclManager */
        $aclManager = $this->container->get('oro_security.acl.manager');
        if (!$aclManager->isAclEnabled()) {
            return;
        }

        $role = $this->getAccountManagerRole($manager);
        if (null === $role) {
            return;
        }

        $sid = $aclManager->getSid($role);
        $this->updateEntityPermissions(
            $aclManager,
            $sid,
            self::ACCOUNT_ENTITY,
            ['VIEW_SYSTEM', 'CREATE_SYSTEM', 'EDIT_SYSTEM', 'DELETE_SYSTEM', 'ASSIGN_SYSTEM']
        );
        $this->updateEntityPermissions(
            $aclManager,
            $sid,
            self::CONTACT_ENTITY,
            ['VIEW_SYSTEM', 'CREATE_SYSTEM', 'EDIT_SYSTEM', 'DELETE_SYSTEM', 'ASSIGN_SYSTEM']
        );

        $aclManager->flush();
    }

    /**
     * @param ObjectManager $manager
     *
     * @return Role|null
     */
    private function getAccountManagerRole(ObjectManager $manager)
    {
        return $manager
            ->getRepository(Role::class)
            ->findOneBy(['role' => self::ACCOUNT_MANAGER_ROLE]);
    }

    /**
     * @param AclManager $aclManager
     * @param SID        $sid
     * @param string     $entityClass
     * @param string[]   $permissions
     */
    private function updateEntityPermissions(AclManager $aclManager, SID $sid, $entityClass, array $permissions)
    {
        $oid = $aclManager->getOid('entity:' . $entityClass);
        $maskBuilders = $aclManager->getExtensionSelector()->selectByExtensionKey('entity')->getAllMaskBuilders();
        foreach ($maskBuilders as $maskBuilder) {
            $hasChanges = false;
            $maskBuilder->reset();
            foreach ($permissions as $permission) {
                if ($maskBuilder->hasMask('MASK_' . $permission)) {
                    $maskBuilder->add($permission);
                    $hasChanges = true;
                }
            }
            if ($hasChanges) {
                $aclManager->setPermission($sid, $oid, $maskBuilder->get());
            }
        }
    }
}
