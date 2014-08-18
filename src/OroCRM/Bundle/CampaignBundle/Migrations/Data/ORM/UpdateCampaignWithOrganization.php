<?php

namespace OroCRM\Bundle\CampaignBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\UpdateWithOrganization;

class UpdateCampaignWithOrganization extends UpdateWithOrganization
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->update($manager, 'OroCRMCampaignBundle:Campaign');
    }
}
