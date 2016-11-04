<?php

namespace Oro\Bundle\SalesBundle\Tools\Customers;

use Oro\Bundle\EntityExtendBundle\Tools\DumperExtensions\AssociationEntityConfigDumperExtension;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;

class LeadConfigDumperExtension extends AssociationEntityConfigDumperExtension
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
        return 'lead';
    }

    /**
     * {@inheritdoc}
     */
    protected function getAssociationKind()
    {
        return CustomerScope::ASSOCIATION_KIND;
    }
}
