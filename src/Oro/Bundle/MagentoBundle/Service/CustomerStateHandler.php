<?php

namespace Oro\Bundle\MagentoBundle\Service;

use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\MagentoBundle\Entity\Customer;

class CustomerStateHandler
{
    /**
     * @var StateManager
     */
    protected $stateManager;

    /**
     * @param StateManager $stateManager
     */
    public function __construct(StateManager $stateManager)
    {
        $this->stateManager = $stateManager;
    }

    /**
     * @param Customer $entity
     * @return bool
     */
    public function isCustomerRemoved(Customer $entity)
    {
        return $this->stateManager->isInState($entity->getSyncState(), Customer::MAGENTO_REMOVED);
    }

    /**
     * @param Address $entity
     * @return bool
     */
    public function isAddressRemoved(Address $entity)
    {
        return $this->stateManager->isInState($entity->getSyncState(), Address::MAGENTO_REMOVED);
    }

    /**
     * @param Address $entity
     */
    public function markAddressForSync(Address $entity)
    {
        if (!$this->isAddressRemoved($entity)) {
            $this->stateManager->addState($entity, 'syncState', Address::SYNC_TO_MAGENTO);
        }
    }

    /**
     * @param Customer $entity
     */
    public function markCustomerForSync(Customer $entity)
    {
        if (!$this->isCustomerRemoved($entity) && !$entity->isGuest()) {
            $this->stateManager->addState($entity, 'syncState', Customer::SYNC_TO_MAGENTO);
        }
    }

    /**
     * @param Customer $customer
     */
    public function markCustomerSynced(Customer $customer)
    {
        if (!$this->isCustomerRemoved($customer)) {
            $this->stateManager->removeState($customer, 'syncState', Customer::SYNC_TO_MAGENTO);
        }
    }

    /**
     * @param Address $address
     */
    public function markAddressSynced(Address $address)
    {
        if (!$this->isAddressRemoved($address)) {
            $this->stateManager->removeState($address, 'syncState', Address::SYNC_TO_MAGENTO);
        }
    }

    /**
     * @param Customer $customer
     */
    public function markAddressesForSync(Customer $customer)
    {
        if (!$customer->getAddresses()->isEmpty()) {
            /** @var Address $address */
            foreach ($customer->getAddresses() as $address) {
                $this->markAddressForSync($address);
            }
        }
    }

    /**
     * @param Customer $customer
     */
    public function markCustomerRemoved(Customer $customer)
    {
        $customer->setSyncState(Customer::MAGENTO_REMOVED);
        $this->markAddressesRemoved($customer);
    }

    /**
     * @param Address $address
     */
    public function markAddressRemoved(Address $address)
    {
        $address->setSyncState(Address::MAGENTO_REMOVED);
    }

    /**
     * @param Customer $customer
     */
    public function markAddressesRemoved(Customer $customer)
    {
        if (!$customer->getAddresses()->isEmpty()) {
            /** @var Address $address */
            foreach ($customer->getAddresses() as $address) {
                $this->markAddressRemoved($address);
            }
        }
    }
}
