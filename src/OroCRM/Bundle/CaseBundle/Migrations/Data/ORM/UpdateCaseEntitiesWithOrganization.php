<?php

namespace OroCRM\Bundle\CaseBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\UpdateWithOrganization;

class UpdateCaseEntitiesWithOrganization extends UpdateWithOrganization
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->update($manager, 'OroCRMCaseBundle:CaseEntity');
        $this->update($manager, 'OroCRMCaseBundle:CaseComment');
    }
}
