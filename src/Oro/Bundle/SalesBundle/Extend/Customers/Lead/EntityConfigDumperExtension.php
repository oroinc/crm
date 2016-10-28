<?php

namespace Oro\Bundle\SalesBundle\Extend\Customers\Lead;

use Oro\Bundle\EntityExtendBundle\Tools\DumperExtensions\AssociationEntityConfigDumperExtension;
use Oro\Bundle\SalesBundle\Entity\Lead;

class EntityConfigDumperExtension extends AssociationEntityConfigDumperExtension
{
    /**
     * {@inheritdoc}
     */
    protected function getAssociationEntityClass()
    {
        return Lead::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAssociationScope()
    {
        return 'sales_lead';
    }
}
