<?php

namespace Oro\Bundle\SalesBundle\Api\Processor;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

use Oro\Bundle\ApiBundle\Config\FilterIdentifierFieldsConfigExtra;
use Oro\Bundle\ApiBundle\Processor\Config\ConfigContext;

class CustomerAssociationGetConfig implements ProcessorInterface
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
     * Removes 'identifier_fields_only' extra to allow loading required dependent account and target data
     *
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var ConfigContext $context */

        $definition = $context->getResult();
        if ($definition->isExcludeAll()) {
            // already processed
            return;
        }

        if ($context->hasExtra(FilterIdentifierFieldsConfigExtra::NAME)) {
            $extras    = $context->getExtras();
            $newExtras = [];
            foreach ($extras as $extra) {
                if ($extra->getName() !== FilterIdentifierFieldsConfigExtra::NAME) {
                    $newExtras[] = $extra;
                }
            }
            $context->setExtras($newExtras);
        }
    }
}
