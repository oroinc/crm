<?php

namespace OroCRM\Bundle\AccountBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\UpdateWithOrganization;

class UpdateAccountWithOrganization extends UpdateWithOrganization
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->update($manager, 'OroCRMAccountBundle:Account');
    }
}
