<?php

namespace Oro\Bundle\SalesBundle\Api\Processor;

use Oro\Bundle\ApiBundle\Processor\ContextMetadataAccessor;
use Oro\Bundle\ApiBundle\Processor\FormContext;
use Oro\Bundle\ApiBundle\Processor\Shared\SwitchFormExtension;
use Oro\Bundle\SalesBundle\Form\Guesser\CustomerAccountApiTypeGuesser;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

class InitializeCustomerAccountTypeGuesser implements ProcessorInterface
{
    /** @var CustomerAccountApiTypeGuesser */
    protected $guesser;

    /**
     * @param CustomerAccountApiTypeGuesser $guesser
     */
    public function __construct(CustomerAccountApiTypeGuesser $guesser)
    {
        $this->guesser = $guesser;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        if (!$this->isApiFormExtensionActivated($context)) {
            return;
        }

        $this->guesser->setMetadataAccessor(new ContextMetadataAccessor($context));
    }

    /**
     * @param FormContext $context
     *
     * @return bool
     */
    protected function isApiFormExtensionActivated(FormContext $context)
    {
        return (bool) $context->get(SwitchFormExtension::API_FORM_EXTENSION_ACTIVATED);
    }
}
