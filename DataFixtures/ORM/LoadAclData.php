<?php
namespace Oro\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Oro\Bundle\UserBundle\Entity\Acl;

class LoadAclData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load Root ACL Resource
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $rootAcl = new Acl();
        $rootAcl->setId('root')
            ->setDescription('root node')
            ->setName('Root')
            ->addAccessRole($this->getReference('user_role'))
            ->addAccessRole($this->getReference('admin_role'))
        ;
        $manager->persist($rootAcl);
        $this->setReference('acl_root', $rootAcl);

        // security section
        $oroSecurity = new Acl();
        $oroSecurity->setId('oro_security')
            ->setName('Oro Security')
            ->setDescription('Oro security')
            ->setParent($this->getReference('acl_root'))
            ->addAccessRole($this->getReference('anon_role'))
        ;
        $manager->persist($oroSecurity);
        $this->setReference('acl_oro_security', $oroSecurity);

        $oroLogin = new Acl();
        $oroLogin->setId('oro_login')
            ->setName('Login page')
            ->setDescription('Oro Login page')
            ->setParent($this->getReference('acl_oro_security'));
        $manager->persist($oroLogin);
        $this->setReference('acl_oro_login', $oroLogin);

        $oroLoginCheck = new Acl();
        $oroLoginCheck->setId('oro_login_check')
            ->setName('Login check')
            ->setDescription('Oro Login check')
            ->setParent($this->getReference('acl_oro_security'));
        $manager->persist($oroLoginCheck);
        $this->setReference('oro_login_check', $oroLoginCheck);

        $oroLogout = new Acl();
        $oroLogout->setId('oro_logout')
            ->setName('Logout')
            ->setDescription('Oro Logout')
            ->setParent($this->getReference('acl_oro_security'));
        $manager->persist($oroLogout);
        $this->setReference('oro_logout', $oroLogout);

        $manager->flush();
    }

    public function getOrder()
    {
        return 100;
    }
}
