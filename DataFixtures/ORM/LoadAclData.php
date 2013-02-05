<?php
namespace Oro\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\UserBundle\Entity\Acl;

class LoadAclData extends AbstractFixture
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
            ->setName('Root');
        $manager->persist($rootAcl);
        $manager->flush();
    }
}
