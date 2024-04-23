<?php

declare(strict_types=1);

namespace Oro\Bundle\SalesBundle\EntityExtend;

use Oro\Bundle\EntityExtendBundle\EntityExtend\AbstractAssociationEntityFieldExtension;
use Oro\Bundle\EntityExtendBundle\EntityExtend\EntityFieldProcessTransport;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\EntityExtendBundle\Tools\AssociationNameGenerator;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;

/**
 * Extended Entity Field Processor Extension for customer associations
 */
class CustomerEntityFieldExtension extends AbstractAssociationEntityFieldExtension
{
    public function isApplicable(EntityFieldProcessTransport $transport): bool
    {
        return $transport->getClass() === Customer::class
            && AssociationNameGenerator::extractAssociationKind($transport->getName()) === $this->getRelationKind();
    }

    public function getRelationKind(): ?string
    {
        return CustomerScope::ASSOCIATION_KIND;
    }

    public function getRelationType(): string
    {
        return RelationType::MANY_TO_ONE;
    }
}
