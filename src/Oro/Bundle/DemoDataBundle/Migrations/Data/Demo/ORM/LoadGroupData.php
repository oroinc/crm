<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Component\DependencyInjection\ContainerAwareInterface;
use Oro\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Load User groups for default organization
 */
class LoadGroupData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadBusinessUnitData::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $organization = $this->getReference('default_organization');
        $defaultCrmBU = $this->getBusinessUnit($manager, 'Acme, West');
        $defaultCoreBU = $this->getBusinessUnit($manager, 'Acme, East');
        $defaultMainBU = $this->getBusinessUnit($manager, 'Acme, General');

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
            $manager->persist($newGroup);
        }
        $manager->flush();
    }

    private function getBusinessUnit(ObjectManager $manager, string $name): BusinessUnit
    {
        return $manager->getRepository(BusinessUnit::class)->findOneBy(['name' => $name]);
    }
}
