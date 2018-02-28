<?php

namespace Oro\Bundle\MagentoBundle\Customer;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MagentoBundle\Entity\Customer as MagentoCustomer;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;

class AssociationChecker
{
    /** @var AccountCustomerManager */
    protected $manager;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param AccountCustomerManager $manager
     * @param DoctrineHelper         $doctrineHelper
     */
    public function __construct(AccountCustomerManager $manager, DoctrineHelper $doctrineHelper)
    {
        $this->manager        = $manager;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param MagentoCustomer $magentoCustomer
     */
    public function fixAssociation(MagentoCustomer $magentoCustomer)
    {
        // If Magento Customer do not have yet customer association we need to manually create it
        $customerAssociation = $this->manager->getAccountCustomerByTarget($magentoCustomer, false);
        if (!$customerAssociation) {
            // try to get Account from direct relation first
            // and create new via AccountCustomerManager
            $account             = $magentoCustomer->getAccount()
                ? : $this->manager->createAccountForTarget($magentoCustomer);
            $customerAssociation = AccountCustomerManager::createCustomer($account, $magentoCustomer);
            $em                  = $this->doctrineHelper->getEntityManagerForClass(Customer::class);
            $em->persist($customerAssociation);
            $em->flush($customerAssociation);
        }
    }
}
