<?php

namespace Oro\Bundle\SalesBundle\Tools\Customers;

use Oro\Bundle\EntityExtendBundle\Tools\DumperExtensions\AssociationEntityConfigDumperExtension;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;

class OpportunityConfigDumperExtension extends AssociationEntityConfigDumperExtension
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
        return 'opportunity';
    }

    /**
     * {@inheritdoc}
     */
    protected function getAssociationKind()
    {
        return CustomerScope::ASSOCIATION_KIND;
    }
}
