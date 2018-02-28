<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadOwnerUser extends AbstractFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var UserManager $userManager */
        $userManager = $this->container->get('oro_user.manager');

        $role  = $manager
            ->getRepository('OroUserBundle:Role')
            ->findOneByRole('ROLE_ADMINISTRATOR');
        $group = $manager
            ->getRepository('OroUserBundle:Group')
            ->findOneByName('Administrators');

        $unit = $manager
            ->getRepository('OroOrganizationBundle:BusinessUnit')
            ->findOneByName('Main');

        $organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        $user = new User();
        $user->setUsername('owner_User');
        $user->addGroup($group);
        $user->addRole($role);
        $user->addBusinessUnit($unit);
        $user->setFirstname('Test Owner  FirstName');
        $user->setLastname('Test Owner LastName');
        $user->setEmail('owner@example.com');
        $user->setOwner($unit);
        $user->addGroup($group);
        $user->setPlainPassword('test password');
        $user->setSalt(md5(mt_rand(1, 222)));
        $user->setOrganization($organization);

        $userManager->updateUser($user);
        $this->setReference('owner_user', $user);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
