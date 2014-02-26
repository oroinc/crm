<?php
namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;

class LoadGroupData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadBusinessUnitData'];
    }

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
        //$role = $entityManager->getRepository('OroUserBundle:Role')->findOneBy(array('role' => 'ROLE_MANAGER'));
        $defaultCrmBU = $this->getBusinessUnit($manager, 'Acme, West');
        $defaultCoreBU = $this->getBusinessUnit($manager, 'Acme, East');
        $defaultMainBU = $this->getBusinessUnit($manager, 'Acme, General');
        $groups = array(
            'Marketing Manager' =>  $defaultCrmBU,
            'Executive Marketing' =>  $defaultCrmBU,
            'Sales Manager' => $defaultCoreBU,
            'Executive Sales' => $defaultCoreBU,
            'Promotion Manager' => $defaultMainBU,
            'Executive Director' => $defaultMainBU
        );

        foreach ($groups as $group => $user) {
            $newGroup = new Group($group);
            $newGroup->setOwner($user);
            //$newGroup->setRoles(array($role));
            $entityManager->persist($newGroup);
        }
        $entityManager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param $name
     * @return BusinessUnit
     */
    protected function getBusinessUnit(ObjectManager $manager, $name)
    {
        return $manager->getRepository('OroOrganizationBundle:BusinessUnit')->findOneBy(['name' => $name]);
    }
}
