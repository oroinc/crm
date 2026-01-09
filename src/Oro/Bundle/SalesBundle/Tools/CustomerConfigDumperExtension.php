<?php

namespace Oro\Bundle\SalesBundle\Tools;

use Oro\Bundle\EntityExtendBundle\Tools\DumperExtensions\AssociationEntityConfigDumperExtension;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;

/**
 * Extends configuration dumper to include customer-related entity configuration.
 */
class CustomerConfigDumperExtension extends AssociationEntityConfigDumperExtension
{
    #[\Override]
    protected function getAssociationEntityClass()
    {
        return Customer::class;
    }

    #[\Override]
    protected function getAssociationScope()
    {
        return 'customer';
    }

    #[\Override]
    protected function getAssociationKind()
    {
        return CustomerScope::ASSOCIATION_KIND;
    }
}
