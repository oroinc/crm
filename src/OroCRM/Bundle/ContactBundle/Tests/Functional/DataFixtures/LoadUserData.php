<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\Manager\OrganizationManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\UserBundle\Entity\Role;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface
{
    const USER_NAME         = 'user';
    const USER_PASSWORD     = 'password';
    const USER_ORGANIZATION = 1;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public static $roleData = [
        'name'        => 'ROLE_TEST',
        'label'       => 'Role Test',
        'permissions' => [
            'entity|OroCRM\Bundle\ContactBundle\Entity\Contact' => [
                'VIEW_BASIC', 'EDIT_SYSTEM'
            ]
        ]
    ];

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
        $role = new Role(self::$roleData['name']);
        $role->setLabel(self::$roleData['label']);
        $manager->persist($role);

        /** @var AclManager $aclManager */
        $aclManager = $this->container->get('oro_security.acl.manager');

        if ($aclManager->isAclEnabled()) {
            $sid = $aclManager->getSid($role);
            foreach (LoadUserData::$roleData['permissions'] as $permission => $acls) {
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

        /** @var UserManager $userManager */
        $userManager = $this->container->get('oro_user.manager');

        $user = $userManager->createUser();

        /** @var OrganizationManager $organizationManager */
        $organizationManager = $this->container->get('oro_organization.organization_manager');
        $organization = $organizationManager->getOrganizationRepo()->getOrganizationById(self::USER_ORGANIZATION);

        $user->setUsername(self::USER_NAME)
            ->setPlainPassword(self::USER_PASSWORD)
            ->setFirstName('User')
            ->setLastName('Test')
            ->addRole($role)
            ->setEmail('user@example.com')
            ->setOrganization($organization)
            ->addOrganization($organization)
            ->setSalt('');

        $userManager->updateUser($user);

        $aclManager->flush();
        $manager->flush();
    }
}
