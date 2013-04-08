<?php
namespace Oro\Bundle\NavigationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\Role;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
{
    const USER_CLASS = 'Oro\Bundle\UserBundle\Entity\User';
    const TEST_NAME  = 'TestUsername';
    const TEST_PASSWORD  = '12345';
    const TEST_EMAIL = 'test@test.com';

    /**
     * Load User Resource
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $ef = new EncoderFactory(array(self::USER_CLASS => new MessageDigestPasswordEncoder('sha512')));

        /** @var $role Role */
        $role = $manager->getRepository('Oro\Bundle\UserBundle\Entity\Role')
            ->findOneBy(array('role' => 'ROLE_USER'));

        $user = new User();
        $user->setUsername(self::TEST_NAME)
            ->setPassword($ef->getEncoder($user)->encodePassword(self::TEST_PASSWORD, $user->getSalt()))
            ->setEmail(self::TEST_EMAIL)
            ->setFirstname('Firstname')
            ->setLastname('Lastname')
            ->setEnabled(true);
        $user->addRole($role);

        $manager->persist($user);
        $manager->flush();
    }

    /**
     * Get the order in which fixtures will be loaded
     *
     * @return int
     */
    public function getOrder()
    {
        return 100;
    }
}
