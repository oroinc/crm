<?php

namespace Oro\Bundle\SalesBundle\Api\Processor;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;

/**
 * This class contains a set of helper methods for API processors
 * responsible for modification of the customer association.
 */
class ChangeCustomerAssociationHelper
{
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
}
