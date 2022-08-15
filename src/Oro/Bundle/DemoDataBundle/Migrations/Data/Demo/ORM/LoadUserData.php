<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadRolesData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads Sale and Marketing users for default organization.
 */
class LoadUserData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    private ContainerInterface $container;

    /**
     * {@inheritDoc}
     */
    public function getDependencies(): array
    {
        return [
            LoadGroupData::class,
            LoadBusinessUnitData::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        /** @var UserManager $userManager */
        $userManager = $this->container->get('oro_user.manager');

        /** @var Organization $organization */
        $organization = $this->getReference('default_organization');
        $marketingRole = $this->getRole($manager, 'ROLE_MARKETING_MANAGER');
        $saleRole = $this->getRole($manager, LoadRolesData::ROLE_MANAGER);
        $salesGroup = $this->getGroup($manager, 'Executive Sales');
        $marketingGroup = $this->getGroup($manager, 'Executive Marketing');

        $sale = $userManager->createUser();
        $sale
            ->setUsername('sale')
            ->setPlainPassword('sale')
            ->setFirstName('Ellen')
            ->setLastName('Rowell')
            ->addUserRole($saleRole)
            ->addGroup($salesGroup)
            ->setEmail('sale@example.com')
            ->setOrganization($organization)
            ->addOrganization($organization)
            ->setBusinessUnits(new ArrayCollection([
                $this->getBusinessUnit($manager, 'Acme, General'),
                $this->getBusinessUnit($manager, 'Acme, East'),
                $this->getBusinessUnit($manager, 'Acme, West')
            ]));
        if ($this->hasReference('default_main_business')) {
            $sale->setOwner($this->getBusinessUnit($manager, 'Acme, General'));
        }
        $this->addReference('default_sale', $sale);
        $userManager->updateUser($sale);

        /** @var User $marketing */
        $marketing = $userManager->createUser();
        $marketing
            ->setUsername('marketing')
            ->setPlainPassword('marketing')
            ->setFirstName('Michael')
            ->setLastName('Buckley')
            ->addUserRole($marketingRole)
            ->addGroup($marketingGroup)
            ->setEmail('marketing@example.com')
            ->setOrganization($organization)
            ->addOrganization($organization)
            ->setBusinessUnits(new ArrayCollection([
                $this->getBusinessUnit($manager, 'Acme, General'),
                $this->getBusinessUnit($manager, 'Acme, East'),
                $this->getBusinessUnit($manager, 'Acme, West')
            ]));
        if ($this->hasReference('default_main_business')) {
            $marketing->setOwner($this->getBusinessUnit($manager, 'Acme, General'));
        }
        $this->addReference('default_marketing', $marketing);
        $userManager->updateUser($marketing);
    }

    private function getRole(ObjectManager $manager, string $role): Role
    {
        return $manager->getRepository(Role::class)->findOneBy(['role' => $role]);
    }

    private function getGroup(ObjectManager $manager, string $name): Group
    {
        return $manager->getRepository(Group::class)->findOneBy(['name' => $name]);
    }

    private function getBusinessUnit(ObjectManager $manager, string $name): BusinessUnit
    {
        return $manager->getRepository(BusinessUnit::class)->findOneBy(['name' => $name]);
    }
}
