<?php

namespace Oro\Bundle\SalesBundle\Api\Form\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ApiBundle\Form\FormUtil;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SecurityBundle\Form\FieldAclHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * The form event listener responsible for save the customer association related fields.
 */
class CustomerAssociationListener implements EventSubscriberInterface
{
    private const ACCOUNT_FIELD_NAME  = 'account';
    private const CUSTOMER_FIELD_NAME = 'customer';

    /** @var AccountCustomerManager */
    private $accountCustomerManager;

    /** @var FieldAclHelper */
    private $fieldAclHelper;

    /** @var bool */
    private $isRelationOptional = false;

    /**
     * @param AccountCustomerManager $accountCustomerManager
     * @param FieldAclHelper         $fieldAclHelper
     */
    public function __construct(
        AccountCustomerManager $accountCustomerManager,
        FieldAclHelper $fieldAclHelper
    ) {
        $this->accountCustomerManager = $accountCustomerManager;
        $this->fieldAclHelper = $fieldAclHelper;
    }

    /**
     * @param bool $isRelationOptional
     */
    public function setIsRelationOptional(bool $isRelationOptional)
    {
        $this->isRelationOptional = $isRelationOptional;
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
    private function handleAccountRelationship(FormInterface $form)
    {
        $accountField = $form->get(self::ACCOUNT_FIELD_NAME);
        if (FormUtil::isSubmittedAndValid($accountField)) {
            $this->setCustomerAssociationForAccount($form->getData(), $accountField->getData(), $accountField);
        }
    }

    /**
     * @param FormInterface $form
     */
    private function handleCustomerRelationship(FormInterface $form)
    {
        $customerField = $form->get(self::CUSTOMER_FIELD_NAME);
        if (FormUtil::isSubmittedAndValid($customerField)) {
            $this->setCustomerAssociationForCustomer($form->getData(), $customerField->getData(), $customerField);
        }
    }

    /**
     * @param FormInterface $form
     */
    private function handlePrimaryForm(FormInterface $form)
    {
        $accountField = $form->get(self::ACCOUNT_FIELD_NAME);
        $customerField = $form->get(self::CUSTOMER_FIELD_NAME);

        $submittedAccount = null;
        $submittedCustomer = null;
        $hasSubmittedData = false;
        if (FormUtil::isSubmittedAndValid($accountField)) {
            $submittedAccount = $accountField->getData();
            $hasSubmittedData = true;
        }
        if (FormUtil::isSubmittedAndValid($customerField)) {
            $submittedCustomer = $customerField->getData();
            $hasSubmittedData = true;
        }

        if (null === $form->getData()->getId()) {
            $this->changeCustomerAssociation($form, $submittedAccount, $submittedCustomer);
        } elseif ($hasSubmittedData
            && FormUtil::isNotSubmittedOrSubmittedAndValid($accountField)
            && FormUtil::isNotSubmittedOrSubmittedAndValid($customerField)
        ) {
            $this->changeCustomerAssociation($form, $submittedAccount, $submittedCustomer);
        }
    }

    /**
     * @param object $ownerEntity
     *
     * @return Customer|null
     */
    private function getCustomerAssociation($ownerEntity)
    {
        return $ownerEntity->getCustomerAssociation();
    }

    /**
     * @param object   $ownerEntity
     * @param Customer $customerAssociation
     */
    private function setCustomerAssociation($ownerEntity, Customer $customerAssociation = null)
    {
        $ownerEntity->setCustomerAssociation($customerAssociation);
    }

    /**
     * @param object        $ownerEntity
     * @param Account|null  $account
     * @param FormInterface $accountField
     */
    private function setCustomerAssociationForAccount($ownerEntity, $account, FormInterface $accountField)
    {
        if (!$this->isCustomerAssociationModificationGranted($ownerEntity)) {
            $this->fieldAclHelper->addFieldModificationDeniedFormError($accountField);
        } elseif (null === $account) {
            FormUtil::addFormConstraintViolation($accountField, new NotNull());
        } else {
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
    }

    /**
     * @param object        $ownerEntity
     * @param object|null   $customer
     * @param FormInterface $customerField
     */
    private function setCustomerAssociationForCustomer($ownerEntity, $customer, FormInterface $customerField)
    {
        if (!$this->isCustomerAssociationModificationGranted($ownerEntity)) {
            $this->fieldAclHelper->addFieldModificationDeniedFormError($customerField);
        } elseif (null === $customer) {
            FormUtil::addFormConstraintViolation($customerField, new NotNull());
        } else {
            $customerAssociation = $this->findCustomerAssociationForCustomer($customer);
            if (null === $customerAssociation) {
                $customerAssociation = $this->createCustomerAssociationForCustomer($customer);
            }
            $this->setCustomerAssociation($ownerEntity, $customerAssociation);
        }
    }

    /**
     * @param object $customer
     *
     * @return Customer|null
     */
    private function findCustomerAssociationForCustomer($customer)
    {
        return $this->accountCustomerManager->getAccountCustomerByTarget($customer, false);
    }

    /**
     * @param Account $account
     *
     * @return Customer
     */
    private function createCustomerAssociationForAccount($account)
    {
        return AccountCustomerManager::createCustomer($account);
    }

    /**
     * @param object $customer
     *
     * @return Customer
     */
    private function createCustomerAssociationForCustomer($customer)
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
    private function isCustomerAssociationForAccountEquals(Customer $existingCustomerAssociation, Account $account)
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
    private function changeCustomerAssociation(
        FormInterface $form,
        Account $submittedAccount = null,
        $submittedCustomer = null
    ) {
        if (null === $submittedAccount && null === $submittedCustomer) {
            if (!$this->isRelationOptional) {
                FormUtil::addFormError($form, 'Either an account or a customer should be set.');
            }
        } else {
            $entity = $form->getData();

            $accountField = $form->get(self::ACCOUNT_FIELD_NAME);
            $customerField = $form->get(self::CUSTOMER_FIELD_NAME);
            if (FormUtil::isSubmittedAndValid($accountField) && FormUtil::isSubmittedAndValid($customerField)) {
                if (null === $submittedCustomer) {
                    $this->setCustomerAssociationForAccount($entity, $submittedAccount, $accountField);
                } else {
                    $this->setCustomerAssociationForCustomer($entity, $submittedCustomer, $customerField);
                    if (null !== $submittedAccount && $customerField->isValid()) {
                        $account = $this->getCustomerAssociation($entity)->getAccount();
                        if ($submittedAccount->getId() !== $account->getId()) {
                            FormUtil::addFormError(
                                $customerField,
                                'The customer should be a part of the specified account.'
                            );
                        }
                    }
                }
            } elseif (null !== $submittedCustomer) {
                $this->setCustomerAssociationForCustomer($entity, $submittedCustomer, $customerField);
            } else {
                $this->setCustomerAssociationForAccount($entity, $submittedAccount, $accountField);
            }
        }
    }

    /**
     * @param object $entity
     *
     * @return bool
     */
    private function isCustomerAssociationModificationGranted($entity)
    {
        if (!$this->fieldAclHelper->isFieldAclEnabled(ClassUtils::getClass($entity))) {
            return true;
        }

        return $this->fieldAclHelper->isFieldModificationGranted($entity, 'customerAssociation');
    }
}
