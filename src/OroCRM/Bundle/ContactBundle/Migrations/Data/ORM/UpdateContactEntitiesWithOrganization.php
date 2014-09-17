<?php

namespace OroCRM\Bundle\ContactBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\UpdateWithOrganization;

class UpdateContactEntitiesWithOrganization extends UpdateWithOrganization implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\ContactBundle\Migrations\Data\ORM\LoadGroupData',
            'Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->update($manager, 'OroCRMContactBundle:Group');
        $this->update($manager, 'OroCRMContactBundle:Contact');
    }
}
