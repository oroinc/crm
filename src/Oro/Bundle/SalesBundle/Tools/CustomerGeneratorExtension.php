<?php

namespace Oro\Bundle\SalesBundle\Tools;

use Oro\Bundle\EntityExtendBundle\Tools\GeneratorExtensions\AbstractAssociationEntityGeneratorExtension;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;

class CustomerGeneratorExtension extends AbstractAssociationEntityGeneratorExtension
{
    /**
     * {@inheritdoc}
     */
    public function supports(array $schema)
    {
        return
            $schema['class'] === Customer::class
            && parent::supports($schema);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getAssociationKind()
    {
        return CustomerScope::ASSOCIATION_KIND;
    }
}
