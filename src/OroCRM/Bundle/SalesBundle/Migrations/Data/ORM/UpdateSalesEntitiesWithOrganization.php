<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\UpdateWithOrganization;

class UpdateSalesEntitiesWithOrganization extends UpdateWithOrganization
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->update($manager, 'OroCRMSalesBundle:Lead');
        $this->update($manager, 'OroCRMSalesBundle:Opportunity');
    }
}
