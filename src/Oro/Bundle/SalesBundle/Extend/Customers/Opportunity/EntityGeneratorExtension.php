<?php

namespace Oro\Bundle\SalesBundle\Extend\Customers\Opportunity;

use Oro\Bundle\EntityExtendBundle\Tools\GeneratorExtensions\AbstractAssociationEntityGeneratorExtension;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class EntityGeneratorExtension extends AbstractAssociationEntityGeneratorExtension
{
    /**
     * {@inheritdoc}
     */
    public function supports(array $schema)
    {
        return
            $schema['class'] === Opportunity::class
            && parent::supports($schema);
    }
}
