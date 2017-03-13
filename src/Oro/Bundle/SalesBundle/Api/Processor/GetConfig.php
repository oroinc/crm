<?php

namespace Oro\Bundle\SalesBundle\Api\Processor;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

class GetConfig implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        $entityDefinitionConfig = $context->getResult();
        if (!$entityDefinitionConfig->hasField('customerAssociation')) {
            return;
        }

        $customerField = clone $entityDefinitionConfig->getField('customerAssociation');
        $customerField->setExcluded(false);
        $entityDefinitionConfig->addField('customer', $customerField);
    }
}
