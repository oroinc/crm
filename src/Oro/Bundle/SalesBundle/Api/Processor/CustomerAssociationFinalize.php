<?php

namespace Oro\Bundle\SalesBundle\Api\Processor;

use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Component\ChainProcessor\ContextInterface;

use Oro\Bundle\ApiBundle\Processor\ListContext;

class CustomerAssociationFinalize implements ProcessorInterface
{
    /** @var string */
    protected $customerAssociationField;

    /**
     * @param string $customerAssociationField
     */
    public function __construct($customerAssociationField)
    {
        $this->customerAssociationField = $customerAssociationField;
    }

    /**
     * Removes dependent associations metadata to prevent loading customer association relation of the root entity
     * in 'included' section
     *
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var ListContext $context */
        $metadata = $context->getMetadata();
        if (null === $metadata) {
            return;
        }

        $customerAssociationMetadata = $metadata->getAssociation($this->customerAssociationField);

        if (null === $customerAssociationMetadata) {
            return;
        }
        $targetMetadata = $customerAssociationMetadata->getTargetMetadata();
        if ($targetMetadata) {
            foreach ($targetMetadata->getAssociations() as $association) {
                $targetMetadata->removeAssociation($association->getName());
            }
        }
    }
}
