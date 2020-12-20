<?php

namespace Oro\Bundle\SalesBundle\Api\Processor;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ApiBundle\Form\FormUtil;
use Oro\Bundle\ApiBundle\Processor\CustomizeFormData\CustomizeFormDataContext;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SecurityBundle\Form\FieldAclHelper;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Handles the customer association related fields.
 */
class HandleCustomerAssociation implements ProcessorInterface
{
    private const ACCOUNT_FIELD_NAME  = 'account';
    private const CUSTOMER_FIELD_NAME = 'customer';

    /** @var AccountCustomerManager */
    private $accountCustomerManager;

    /** @var FieldAclHelper */
    private $fieldAclHelper;

    /** @var bool */
    private $isRelationOptional;

    /**
     * @param AccountCustomerManager $accountCustomerManager
     * @param FieldAclHelper         $fieldAclHelper
     * @param bool                   $isRelationOptional
     */
    public function __construct(
        AccountCustomerManager $accountCustomerManager,
        FieldAclHelper $fieldAclHelper,
        bool $isRelationOptional = false
    ) {
        $this->accountCustomerManager = $accountCustomerManager;
        $this->fieldAclHelper = $fieldAclHelper;
        $this->isRelationOptional = $isRelationOptional;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var CustomizeFormDataContext $context */

        $form = $context->getForm();
        $hasCustomerFieldForm = $form->has(self::CUSTOMER_FIELD_NAME);
        if ($form->has(self::ACCOUNT_FIELD_NAME)) {
            if ($hasCustomerFieldForm) {
                // handle "create" and "update" actions for an entity contains the customer association
                $this->handlePrimaryForm($form);
            } else {
                // handle "update_relationship" action for "account" association
                $this->handleAccountRelationship($form);
            }
        } elseif ($hasCustomerFieldForm) {
            // handle "update_relationship" action for "customer" association
            $this->handleCustomerRelationship($form);
        }
    }

    /**
     * @param FormInterface $form
     */
    private function handleAccountRelationship(FormInterface $form): void
    {
        $accountField = $form->get(self::ACCOUNT_FIELD_NAME);
        if (FormUtil::isSubmittedAndValid($accountField)) {
            $this->setCustomerAssociationForAccount($form->getData(), $accountField->getData(), $accountField);
        }
    }

    /**
     * @param FormInterface $form
     */
    private function handleCustomerRelationship(FormInterface $form): void
    {
        $customerField = $form->get(self::CUSTOMER_FIELD_NAME);
        if (FormUtil::isSubmittedAndValid($customerField)) {
            $this->setCustomerAssociationForCustomer($form->getData(), $customerField->getData(), $customerField);
        }
    }

    /**
     * @param FormInterface $form
     */
    private function handlePrimaryForm(FormInterface $form): void
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
    private function getCustomerAssociation(object $ownerEntity): ?Customer
    {
        return $ownerEntity->getCustomerAssociation();
    }

    /**
     * @param object        $ownerEntity
     * @param Customer|null $customerAssociation
     */
    private function setCustomerAssociation(object $ownerEntity, Customer $customerAssociation = null): void
    {
        $ownerEntity->setCustomerAssociation($customerAssociation);
    }

    /**
     * @param object        $ownerEntity
     * @param Account|null  $account
     * @param FormInterface $accountField
     */
    private function setCustomerAssociationForAccount(
        object $ownerEntity,
        ?Account $account,
        FormInterface $accountField
    ): void {
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
    private function setCustomerAssociationForCustomer(
        object $ownerEntity,
        ?object $customer,
        FormInterface $customerField
    ): void {
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
    private function findCustomerAssociationForCustomer(object $customer): ?Customer
    {
        return $this->accountCustomerManager->getAccountCustomerByTarget($customer, false);
    }

    /**
     * @param Account $account
     *
     * @return Customer
     */
    private function createCustomerAssociationForAccount(Account $account): Customer
    {
        return AccountCustomerManager::createCustomer($account);
    }

    /**
     * @param object $customer
     *
     * @return Customer
     */
    private function createCustomerAssociationForCustomer(object $customer): Customer
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
    private function isCustomerAssociationForAccountEquals(
        Customer $existingCustomerAssociation,
        Account $account
    ): bool {
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function changeCustomerAssociation(
        FormInterface $form,
        Account $submittedAccount = null,
        object $submittedCustomer = null
    ): void {
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
    private function isCustomerAssociationModificationGranted(object $entity): bool
    {
        if (!$this->fieldAclHelper->isFieldAclEnabled(ClassUtils::getClass($entity))) {
            return true;
        }

        return $this->fieldAclHelper->isFieldModificationGranted($entity, 'customerAssociation');
    }
}
