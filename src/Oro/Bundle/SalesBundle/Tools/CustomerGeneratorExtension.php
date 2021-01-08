<?php
declare(strict_types=1);

namespace Oro\Bundle\SalesBundle\Tools;

use Oro\Bundle\EntityExtendBundle\Tools\GeneratorExtensions\AbstractAssociationEntityGeneratorExtension;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;

/**
 * Generates PHP code for CustomerScope::ASSOCIATION_KIND association.
 */
class CustomerGeneratorExtension extends AbstractAssociationEntityGeneratorExtension
{
    public function supports(array $schema): bool
    {
        return
            $schema['class'] === Customer::class
            && parent::supports($schema);
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    protected function getAssociationKind(): ?string
    {
        return CustomerScope::ASSOCIATION_KIND;
    }
}
