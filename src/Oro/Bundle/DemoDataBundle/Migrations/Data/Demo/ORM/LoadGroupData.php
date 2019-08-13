<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Migrations\Data\Demo\ORM\LoadAcmeOrganizationAndBusinessUnitData;
use Oro\Bundle\UserBundle\Entity\Group;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Load User groups for two organizations (default one and demo)
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
            LoadBusinessUnitData::class,
            LoadAcmeOrganizationAndBusinessUnitData::class
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
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
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

        //Save same groups for second organization as well
        $secondOrganization = $this->getReference(LoadAcmeOrganizationAndBusinessUnitData::REFERENCE_DEMO_ORGANIZATION);
        $secondOrganizationBU = $this->getReference(LoadAcmeOrganizationAndBusinessUnitData::REFERENCE_DEMO_BU);

        $groups['Administrators'] = $secondOrganizationBU;
        $groups['Marketing'] = $secondOrganizationBU;
        $groups['Sales'] = $secondOrganizationBU;

        foreach ($groups as $group => $user) {
            $newGroup = new Group($group);
            $newGroup->setOwner($secondOrganizationBU);
            $newGroup->setOrganization($secondOrganization);
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
