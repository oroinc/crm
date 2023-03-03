<?php
declare(strict_types=1);

namespace Oro\Bundle\SalesBundle\EntityExtend;

use Oro\Bundle\EntityExtendBundle\EntityExtend\AbstractAssociationEntityFieldExtension;
use Oro\Bundle\EntityExtendBundle\EntityExtend\EntityFieldProcessTransport;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;

/**
 * Extended Entity Field Processor Extension for customer associations
 */
class CustomerEntityFieldExtension extends AbstractAssociationEntityFieldExtension
{
    protected function isApplicable(EntityFieldProcessTransport $transport): bool
    {
        return $transport->getClass() === Customer::class;
    }

    protected function getRelationKind(): ?string
    {
        return CustomerScope::ASSOCIATION_KIND;
    }

    protected function getRelationType(): string
    {
        return RelationType::MANY_TO_ONE;
    }
}
