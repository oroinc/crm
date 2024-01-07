<?php

namespace Oro\Bundle\CaseBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CaseBundle\Entity\CaseComment;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\UpdateWithOrganization;

/**
 * Updates case entities with organization.
 */
class UpdateCaseEntitiesWithOrganization extends UpdateWithOrganization implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData'];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->update($manager, CaseEntity::class);
        $this->update($manager, CaseComment::class);
    }
}
