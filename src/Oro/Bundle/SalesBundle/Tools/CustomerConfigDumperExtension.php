<?php

namespace Oro\Bundle\SalesBundle\Tools;

use Oro\Bundle\EntityExtendBundle\Tools\DumperExtensions\AssociationEntityConfigDumperExtension;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;

class CustomerConfigDumperExtension extends AssociationEntityConfigDumperExtension
{
    /**
     * {@inheritdoc}
     */
    protected function getAssociationEntityClass()
    {
        return Customer::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAssociationScope()
    {
        return 'customer';
    }

    /**
     * {@inheritdoc}
     */
    protected function getAssociationKind()
    {
        return CustomerScope::ASSOCIATION_KIND;
    }
}
