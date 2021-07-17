<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\UserBundle\Entity\Group;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Load User groups for default organization
 */
class LoadGroupData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadBusinessUnitData::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load sample groups
     */
    public function load(ObjectManager $manager)
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $organization   = $this->getReference('default_organization');
        $defaultCrmBU   = $this->getBusinessUnit($manager, 'Acme, West');
        $defaultCoreBU  = $this->getBusinessUnit($manager, 'Acme, East');
        $defaultMainBU  = $this->getBusinessUnit($manager, 'Acme, General');

        $groups = [
            'Marketing Manager' =>  $defaultCrmBU,
            'Executive Marketing' =>  $defaultCrmBU,
            'Sales Manager' => $defaultCoreBU,
            'Executive Sales' => $defaultCoreBU,
            'Promotion Manager' => $defaultMainBU,
            'Executive Director' => $defaultMainBU,
        ];

        foreach ($groups as $group => $user) {
            $newGroup = new Group($group);
            $newGroup->setOwner($user);
            $newGroup->setOrganization($organization);
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
