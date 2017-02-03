<?php

namespace Oro\Bundle\SalesBundle\Api\Processor;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

use Oro\Bundle\ApiBundle\Processor\Context;
use Oro\Bundle\ApiBundle\Processor\ContextMetadataAccessor;
use Oro\Bundle\ApiBundle\Processor\FormContext;
use Oro\Bundle\ApiBundle\Processor\Shared\SwitchFormExtension;
use Oro\Bundle\SalesBundle\Form\Guesser\CustomerApiTypeGuesser;

class InitializeCustomerTypeGuesser implements ProcessorInterface
{
    /** @var CustomerApiTypeGuesser */
    protected $guesser;

    /** @var string */
    protected $customerAssociationField;

    /**
     * @param CustomerApiTypeGuesser $guesser
     * @param string                 $customerAssociationField
     */
    public function __construct(CustomerApiTypeGuesser $guesser, $customerAssociationField)
    {
        $this->guesser                  = $guesser;
        $this->customerAssociationField = $customerAssociationField;
    }

    /**
     * Initialize special customer association form type guesser
     *
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var FormContext|Context $context */

        if (!$this->isApiFormExtensionActivated($context)) {
            // the API form extension is not activated
            return;
        }
        $this->guesser->setCustomerAssociationField($this->customerAssociationField);
        $this->guesser->setIncludedEntities($context->getIncludedEntities());
        $this->guesser->setMetadataAccessor(new ContextMetadataAccessor($context));
    }

    /**
     * @param FormContext $context
     *
     * @return bool
     */
    protected function isApiFormExtensionActivated(FormContext $context)
    {
        return (bool)$context->get(SwitchFormExtension::API_FORM_EXTENSION_ACTIVATED);
    }
}
