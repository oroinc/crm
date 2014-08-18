<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\UpdateWithOrganization;

class UpdateMagentoEntitiesWithOrganization extends UpdateWithOrganization
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->update($manager, 'OroCRMMagentoBundle:Customer');
        $this->update($manager, 'OroCRMMagentoBundle:Order');
        $this->update($manager, 'OroCRMMagentoBundle:Cart');
    }
}
