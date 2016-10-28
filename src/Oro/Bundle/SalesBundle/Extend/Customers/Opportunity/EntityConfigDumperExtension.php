<?php

namespace Oro\Bundle\SalesBundle\Extend\Customers\Opportunity;

use Oro\Bundle\EntityExtendBundle\Tools\DumperExtensions\AssociationEntityConfigDumperExtension;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class EntityConfigDumperExtension extends AssociationEntityConfigDumperExtension
{
    /**
     * {@inheritdoc}
     */
    protected function getAssociationEntityClass()
    {
        return Opportunity::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAssociationScope()
    {
        return 'sales_opportunity';
    }
}
