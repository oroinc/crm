<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Tests\Functional\DataFixtures\SetRolePermissionsTrait;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadBusinessUnit;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;
    use SetRolePermissionsTrait;

    const USER_NAME     = 'user';
    const USER_PASSWORD = 'password';

    public static $roleData = [
        'name'        => 'ROLE_TEST',
        'label'       => 'Role Test',
        'permissions' => [
            'entity:' . Contact::class => [
                'VIEW_BASIC', 'EDIT_SYSTEM'
            ]
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [LoadOrganization::class, LoadBusinessUnit::class];
    }

    public function load(ObjectManager $manager)
    {
        $role = new Role(self::$roleData['name']);
        $role->setLabel(self::$roleData['label']);
        $manager->persist($role);

        /** @var AclManager $aclManager */
        $aclManager = $this->container->get('oro_security.acl.manager');
        $this->setPermissions(
            $aclManager,
            $role,
            self::$roleData['permissions']
        );

        /** @var UserManager $userManager */
        $userManager = $this->container->get('oro_user.manager');
        $user = $userManager->createUser();
        $organization = $this->getReference('organization');
        $user->setUsername(self::USER_NAME)
            ->setOwner($this->getReference('business_unit'))
            ->setPlainPassword(self::USER_PASSWORD)
            ->setFirstName('User')
            ->setLastName('Test')
            ->addUserRole($role)
            ->setEmail('user@example.com')
            ->setOrganization($organization)
            ->addOrganization($organization)
            ->setSalt('');
        $userManager->updateUser($user);

        $manager->flush();
        $aclManager->flush();
    }
}
