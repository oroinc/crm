<?php

namespace Oro\Bundle\SalesBundle\Api\Processor;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\ApiBundle\Processor\Subresource\UpdateRelationship\UpdateRelationshipContext;

/**
 * Adds event listener for the "update_relationship" form to process
 * the account field of the customer association.
 */
class AddCustomerAssociationAccountFormListener implements ProcessorInterface
{
    /** @var ChangeCustomerAssociationHelper */
    protected $helper;

    /**
     * @param ChangeCustomerAssociationHelper $helper
     */
    public function __construct(ChangeCustomerAssociationHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var UpdateRelationshipContext $context */

        $formBuilder = $context->getFormBuilder();
        if (!$formBuilder) {
            // the form builder does not exist
            return;
        }

        $associationName = $context->getAssociationName();
        $formBuilder->get($associationName)->setMapped(false);

        $formBuilder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($associationName) {
                $this->onPostSubmit($event, $associationName);
            }
        );
    }

    /**
     * @param FormEvent $event
     * @param string    $accountFieldName
     */
    protected function onPostSubmit(FormEvent $event, $accountFieldName)
    {
        $form = $event->getForm();
        $accountField = $form->get($accountFieldName);

        if ($this->helper->isSubmittedAndValid($accountField)) {
            $submittedAccount = $accountField->getData();
            if (null === $submittedAccount) {
                $this->helper->addFormError($accountField, 'This value should not be null.');
            } else {
                $this->helper->setCustomerAssociationForAccount($form->getData(), $submittedAccount);
            }
        }
    }
}
