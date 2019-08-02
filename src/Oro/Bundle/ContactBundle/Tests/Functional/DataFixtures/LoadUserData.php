<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadBusinessUnit;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    const USER_NAME     = 'user';
    const USER_PASSWORD = 'password';

    /**
     * @var ContainerInterface
     */
    protected $container;

    public static $roleData = [
        'name'        => 'ROLE_TEST',
        'label'       => 'Role Test',
        'permissions' => [
            'entity|Oro\Bundle\ContactBundle\Entity\Contact' => [
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
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadOrganization::class, LoadBusinessUnit::class];
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
        $organization = $this->getReference('organization');
        $user->setUsername(self::USER_NAME)
            ->setOwner($this->getReference('business_unit'))
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
