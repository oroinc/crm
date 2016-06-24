<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Oro\Bundle\UserBundle\Model\PermissionCategory;
use Oro\Bundle\UserBundle\Model\PermissionCategoryProviderInterface;

class PermissionCategoryProvider implements PermissionCategoryProviderInterface
{
    const NAME = 'sales_data';
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
        return new PermissionCategory(self::NAME, 'orocrm.sales.permission.category.sales_data.label', true, 7);
    }
}
