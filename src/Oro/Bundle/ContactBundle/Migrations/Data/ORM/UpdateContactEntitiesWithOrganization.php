<?php

namespace Oro\Bundle\ContactBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\UpdateWithOrganization;

/**
 * Sets a default organization to Contact and Group entities.
 */
class UpdateContactEntitiesWithOrganization extends UpdateWithOrganization implements DependentFixtureInterface
{
    #[\Override]
    public function getDependencies(): array
    {
        return [
            LoadGroupData::class,
            LoadOrganizationAndBusinessUnitData::class
        ];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $this->update($manager, Group::class);
        $this->update($manager, Contact::class);
    }
}
