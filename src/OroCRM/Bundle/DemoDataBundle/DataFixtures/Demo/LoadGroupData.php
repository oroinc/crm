<?php
namespace OroCRM\Bundle\DemoDataBundle\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


use Oro\Bundle\UserBundle\Entity\Group;

class LoadGroupData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /** @var ContainerInterface */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load sample groups
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $role = $entityManager->getRepository('OroUserBundle:Role')->findOneBy(array('role' => 'ROLE_MANAGER'));
        $groups = array(
            'Marketing Manager' =>  $this->getReference('default_crm_business'),
            'Executive Marketing' =>  $this->getReference('default_crm_business'),
            'Sales Manager' => $this->getReference('default_core_business'),
            'Executive Sales' => $this->getReference('default_core_business'),
            'Promotion Manager' => $this->getReference('default_main_business'),
            'Executive Director' => $this->getReference('default_main_business')
        );

        foreach ($groups as $group => $user) {
            $newGroup = new Group($group);
            $newGroup->setOwner($user);
            //$newGroup->setRoles(array($role));
            $entityManager->persist($newGroup);
        }
        $entityManager->flush();
    }

    public function getOrder()
    {
        return 101;
    }
}
