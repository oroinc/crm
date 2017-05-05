<?php

namespace Oro\Bundle\SalesBundle\Api\Processor;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ApiBundle\Processor\FormContext;

/**
 * Adds event listener for the "create" and "update" form to process
 * the customer association related fields.
 */
class AddCustomerAssociationFormListener implements ProcessorInterface
{
    const ACCOUNT_FIELD_NAME  = 'account';
    const CUSTOMER_FIELD_NAME = 'customer';

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
        /** @var FormContext $context */

        $formBuilder = $context->getFormBuilder();
        if (!$formBuilder) {
            // the form builder does not exist
            return;
        }

        $formBuilder->get(self::ACCOUNT_FIELD_NAME)->setMapped(false);
        $formBuilder->get(self::CUSTOMER_FIELD_NAME)->setMapped(false);

        $formBuilder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $accountField = $form->get(self::ACCOUNT_FIELD_NAME);
        $customerField = $form->get(self::CUSTOMER_FIELD_NAME);

        $submittedAccount = null;
        $submittedCustomer = null;
        $hasSubmittedData = false;
        if ($this->helper->isSubmittedAndValid($accountField)) {
            $submittedAccount = $accountField->getData();
            $hasSubmittedData = true;
        }
        if ($this->helper->isSubmittedAndValid($customerField)) {
            $submittedCustomer = $customerField->getData();
            $hasSubmittedData = true;
        }

        if ($hasSubmittedData && $this->isSubmittedDataValid($accountField, $customerField)) {
            $this->changeCustomerAssociation($form, $submittedAccount, $submittedCustomer);
        }
    }

    /**
     * @param FormInterface $accountField
     * @param FormInterface $customerField
     *
     * @return bool
     */
    protected function isSubmittedDataValid(FormInterface $accountField, FormInterface $customerField)
    {
        return
            (!$accountField->isSubmitted() || $accountField->isValid())
            && (!$customerField->isSubmitted() || $customerField->isValid());
    }

    /**
     * @param FormInterface $form
     * @param Account|null  $submittedAccount
     * @param object|null   $submittedCustomer
     */
    protected function changeCustomerAssociation(
        FormInterface $form,
        Account $submittedAccount = null,
        $submittedCustomer = null
    ) {
        if (null === $submittedAccount && null === $submittedCustomer) {
            $this->helper->addFormError($form, 'Either an account or a customer should be set.');
        } else {
            $entity = $form->getData();
            $existingCustomerAssociation = $this->helper->getCustomerAssociation($entity);

            $accountField = $form->get(self::ACCOUNT_FIELD_NAME);
            $customerField = $form->get(self::CUSTOMER_FIELD_NAME);
            if ($this->helper->isSubmittedAndValid($accountField)
                && $this->helper->isSubmittedAndValid($customerField)
                && null !== $submittedAccount
                && null !== $submittedCustomer
                && (
                    null === $existingCustomerAssociation
                    || $existingCustomerAssociation->getAccount()->getId() !== $submittedAccount->getId()
                )
            ) {
                $this->helper->addFormError($accountField, 'Either an account or a customer can be changed.');
            } else {
                if (null !== $submittedCustomer) {
                    $this->helper->setCustomerAssociationForCustomer($entity, $submittedCustomer);
                } else {
                    $this->helper->setCustomerAssociationForAccount($entity, $submittedAccount);
                }
            }
        }
    }
}
