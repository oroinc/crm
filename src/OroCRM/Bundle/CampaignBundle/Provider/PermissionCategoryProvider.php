<?php

namespace OroCRM\Bundle\CampaignBundle\Provider;

use Oro\Bundle\UserBundle\Model\PermissionCategory;
use Oro\Bundle\UserBundle\Model\PermissionCategoryProviderInterface;

class PermissionCategoryProvider implements PermissionCategoryProviderInterface
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
    public function getRolePermissionCategory()
    {
        return new PermissionCategory(self::NAME, 'orocrm.campaign.permission.category.marketing.label', true, 5);
    }
}
