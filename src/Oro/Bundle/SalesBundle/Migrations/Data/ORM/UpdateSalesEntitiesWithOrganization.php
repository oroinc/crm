<?php

namespace Oro\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\UpdateWithOrganization;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

/**
 * Updates sales entities with organization.
 */
class UpdateSalesEntitiesWithOrganization extends UpdateWithOrganization implements DependentFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [LoadOrganizationAndBusinessUnitData::class];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->update($manager, Lead::class);
        $this->update($manager, Opportunity::class);
    }
}
