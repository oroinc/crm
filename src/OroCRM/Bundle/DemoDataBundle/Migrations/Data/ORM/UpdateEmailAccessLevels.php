<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

class UpdateEmailAccessLevels extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
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

        /** @var AclManager $manager */
        $manager = $this->container->get('oro_security.acl.manager');

        if ($manager->isAclEnabled()) {
            $this->updateUserRole($manager);
            $manager->flush();
        }
    }

    protected function updateUserRole(AclManager $manager)
    {
        $roles = [
            'ROLE_ONLINE_SALES_REP',
            'ROLE_MARKETING_MANAGER',
            'ROLE_LEADS_DEVELOPMENT_REP',
        ];
        foreach ($roles as $roleName) {
            $sid = $manager->getSid($this->getRole($roleName));

            $oid = $manager->getOid('entity:Oro\Bundle\EmailBundle\Entity\Email');
            $maskBuilder = $manager->getMaskBuilder($oid)
                ->add('VIEW_BASIC')
                ->add('CREATE_BASIC')
                ->add('EDIT_BASIC');
            $manager->setPermission($sid, $oid, $maskBuilder->get());
        }
    }

    /**
     * @param string $roleName
     * @return Role
     */
    protected function getRole($roleName)
    {
        return $this->objectManager->getRepository('OroUserBundle:Role')->findOneBy(['role' => $roleName]);
    }
}
