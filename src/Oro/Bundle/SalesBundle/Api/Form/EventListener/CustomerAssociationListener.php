<?php

namespace Oro\Bundle\SalesBundle\Api\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;

/**
 * The form event listener responsible for save the customer association related fields.
 */
class CustomerAssociationListener implements EventSubscriberInterface
{
    const ACCOUNT_FIELD_NAME  = 'account';
    const CUSTOMER_FIELD_NAME = 'customer';

    /** @var AccountCustomerManager */
    protected $accountCustomerManager;

    /**
     * @param AccountCustomerManager $accountCustomerManager
     */
    public function __construct(AccountCustomerManager $accountCustomerManager)
    {
        $this->accountCustomerManager = $accountCustomerManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [FormEvents::POST_SUBMIT => 'onPostSubmit'];
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        if ($form->has(self::ACCOUNT_FIELD_NAME)) {
            if ($form->has(self::CUSTOMER_FIELD_NAME)) {
                // handle "create" and "update" actions for an entity contains the customer association
                $this->handlePrimaryForm($form);
            } else {
                // handle "update_relationship" action for "account" association
                $this->handleAccountRelationship($form);
            }
        } elseif ($form->has(self::CUSTOMER_FIELD_NAME)) {
            // handle "update_relationship" action for "customer" association
            $this->handleCustomerRelationship($form);
        }
    }

    /**
     * @param FormInterface $form
     */
    protected function handleAccountRelationship(FormInterface $form)
    {
        $accountField = $form->get(self::ACCOUNT_FIELD_NAME);
        if ($this->isSubmittedAndValid($accountField)) {
            $submittedAccount = $accountField->getData();
            if (null === $submittedAccount) {
                $this->addFormError($accountField, 'This value should not be null.');
            } else {
                $this->setCustomerAssociationForAccount($form->getData(), $submittedAccount);
            }
        }
    }

    /**
     * @param FormInterface $form
     */
    protected function handleCustomerRelationship(FormInterface $form)
    {
        $customerField = $form->get(self::CUSTOMER_FIELD_NAME);
        if ($this->isSubmittedAndValid($customerField)) {
            $submittedCustomer = $customerField->getData();
            if (null === $submittedCustomer) {
                $this->addFormError($customerField, 'This value should not be null.');
            } else {
                $this->setCustomerAssociationForCustomer($form->getData(), $submittedCustomer);
            }
        }
    }

    /**
     * @param FormInterface $form
     */
    protected function handlePrimaryForm(FormInterface $form)
    {
        $accountField = $form->get(self::ACCOUNT_FIELD_NAME);
        $customerField = $form->get(self::CUSTOMER_FIELD_NAME);

        $submittedAccount = null;
        $submittedCustomer = null;
        $hasSubmittedData = false;
        if ($this->isSubmittedAndValid($accountField)) {
            $submittedAccount = $accountField->getData();
            $hasSubmittedData = true;
        }
        if ($this->isSubmittedAndValid($customerField)) {
            $submittedCustomer = $customerField->getData();
            $hasSubmittedData = true;
        }

        if ($hasSubmittedData
            && (!$accountField->isSubmitted() || $accountField->isValid())
            && (!$customerField->isSubmitted() || $customerField->isValid())
        ) {
            $this->changeCustomerAssociation($form, $submittedAccount, $submittedCustomer);
        }
    }

    /**
     * @param object $ownerEntity
     *
     * @return Customer|null
     */
    public function getCustomerAssociation($ownerEntity)
    {
        return $ownerEntity->getCustomerAssociation();
    }

    /**
     * @param object   $ownerEntity
     * @param Customer $customerAssociation
     */
    public function setCustomerAssociation($ownerEntity, Customer $customerAssociation = null)
    {
        $ownerEntity->setCustomerAssociation($customerAssociation);
    }

    /**
     * @param object  $ownerEntity
     * @param Account $account
     */
    public function setCustomerAssociationForAccount($ownerEntity, Account $account)
    {
        /** @var Customer|null $existingCustomerAssociation */
        $existingCustomerAssociation = $this->getCustomerAssociation($ownerEntity);
        if (null === $existingCustomerAssociation
            || !$this->isCustomerAssociationForAccountEquals($existingCustomerAssociation, $account)
        ) {
            $this->setCustomerAssociation(
                $ownerEntity,
                $this->createCustomerAssociationForAccount($account)
            );
        }
    }

    /**
     * @param object $ownerEntity
     * @param object $customer
     */
    public function setCustomerAssociationForCustomer($ownerEntity, $customer)
    {
        $customerAssociation = $this->findCustomerAssociationForCustomer($customer);
        if (null === $customerAssociation) {
            $customerAssociation = $this->createCustomerAssociationForCustomer($customer);
        }
        $this->setCustomerAssociation($ownerEntity, $customerAssociation);
    }

    /**
     * @param object $customer
     *
     * @return Customer|null
     */
    protected function findCustomerAssociationForCustomer($customer)
    {
        return $this->accountCustomerManager->getAccountCustomerByTarget($customer, false);
    }

    /**
     * @param Account $account
     *
     * @return Customer
     */
    protected function createCustomerAssociationForAccount($account)
    {
        return AccountCustomerManager::createCustomer($account);
    }

    /**
     * @param object $customer
     *
     * @return Customer
     */
    protected function createCustomerAssociationForCustomer($customer)
    {
        return AccountCustomerManager::createCustomer(
            $this->accountCustomerManager->createAccountForTarget($customer),
            $customer
        );
    }

    /**
     * @param Customer $existingCustomerAssociation
     * @param Account  $account
     *
     * @return bool
     */
    protected function isCustomerAssociationForAccountEquals(Customer $existingCustomerAssociation, Account $account)
    {
        $existingAccount = $existingCustomerAssociation->getAccount();

        return
            $existingAccount->getId()
            && $account->getId()
            && $existingAccount->getId() === $account->getId()
            && null === $existingCustomerAssociation->getCustomerTarget();
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
            $this->addFormError($form, 'Either an account or a customer should be set.');
        } else {
            $entity = $form->getData();
            $existingCustomerAssociation = $this->getCustomerAssociation($entity);

            $accountField = $form->get(self::ACCOUNT_FIELD_NAME);
            $customerField = $form->get(self::CUSTOMER_FIELD_NAME);
            if ($this->isSubmittedAndValid($accountField)
                && $this->isSubmittedAndValid($customerField)
                && null !== $submittedAccount
                && null !== $submittedCustomer
                && (
                    null === $existingCustomerAssociation
                    || $existingCustomerAssociation->getAccount()->getId() !== $submittedAccount->getId()
                )
            ) {
                $this->addFormError($accountField, 'Either an account or a customer can be changed.');
            } else {
                if (null !== $submittedCustomer) {
                    $this->setCustomerAssociationForCustomer($entity, $submittedCustomer);
                } else {
                    $this->setCustomerAssociationForAccount($entity, $submittedAccount);
                }
            }
        }
    }

    /**
     * @param FormInterface $form
     *
     * @return bool
     */
    public function isSubmittedAndValid(FormInterface $form)
    {
        return $form->isSubmitted() && $form->isValid();
    }

    /**
     * @param FormInterface $form
     * @param string        $errorMessage
     */
    public function addFormError(FormInterface $form, $errorMessage)
    {
        $form->addError(new FormError($errorMessage));
    }
}
