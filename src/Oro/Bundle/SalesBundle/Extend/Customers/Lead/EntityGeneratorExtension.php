<?php

namespace Oro\Bundle\SalesBundle\Extend\Customers\Lead;

use Oro\Bundle\EntityExtendBundle\Tools\GeneratorExtensions\AbstractAssociationEntityGeneratorExtension;
use Oro\Bundle\SalesBundle\Entity\Lead;

class EntityGeneratorExtension extends AbstractAssociationEntityGeneratorExtension
{
    /**
     * {@inheritdoc}
     */
    public function supports(array $schema)
    {
        return
            $schema['class'] === Lead::class
            && parent::supports($schema);
    }
}
