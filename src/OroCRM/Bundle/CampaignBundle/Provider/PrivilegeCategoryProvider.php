<?php

namespace OroCRM\Bundle\CampaignBundle\Provider;

use Oro\Bundle\UserBundle\Model\PrivilegeCategory;
use Oro\Bundle\UserBundle\Model\PrivilegeCategoryProviderInterface;

class PrivilegeCategoryProvider implements PrivilegeCategoryProviderInterface
{
    const NAME = 'marketing';
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getRolePrivilegeCategory()
    {
        return new PrivilegeCategory(self::NAME, 'orocrm.campaign.privilege.category.marketing.label', true, 5);
    }
}
