<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Writer;

use Oro\Bundle\IntegrationBundle\Exception\TransportException;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CustomerExportWriter extends AbstractExportWriter
{
    const CUSTOMER_ID_KEY = 'customer_id';
    const FAULT_CODE_NOT_EXISTS = '102';
    const CONTEXT_CUSTOMER_POST_PROCESS = 'postProcessCustomer';

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        /** @var Customer $entity */
        $entity = $this->getEntity();
        if ($this->getStateManager()->isInState($entity->getSyncState(), Customer::MAGENTO_REMOVED)) {
            return;
        }

        $item = reset($items);

        if (!$item) {
            $this->logger->error('Wrong Customer data', (array)$item);

            return;
        }

        $this->transport->init($this->getChannel()->getTransport());
        if (empty($item[self::CUSTOMER_ID_KEY])) {
            $this->writeNewItem($item);
        } else {
            $this->writeExistingItem($item);
        }

        // Clear temporary saved password
        $entity->setPassword(null);
        parent::write([$entity]);
    }

    /**
     * @param array $item
     */
    protected function writeNewItem(array $item)
    {
        /** @var Customer $entity */
        $entity = $this->getEntity();
        try {
            $customerId = $this->transport->createCustomer($item);
            $entity->setOriginId($customerId);
            $this->markSynced($entity);

            $this->logger->info(
                sprintf('Customer with id %s successfully created with data %s', $customerId, json_encode($item))
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->stepExecution->addFailureException($e);
        }
    }

    /**
     * @param Customer $customer
     */
    protected function markSynced(Customer $customer)
    {
        $stateManager = $this->getStateManager();

        if (!$stateManager->isInState($customer->getSyncState(), Customer::MAGENTO_REMOVED)) {
            $stateManager->removeState($customer, 'syncState', Customer::SYNC_TO_MAGENTO);
            $this->markAddressesForSync($customer);
        }
    }

    /**
     * @param Customer $customer
     */
    protected function markAddressesForSync(Customer $customer)
    {
        $stateManager = $this->getStateManager();

        if (!$customer->getAddresses()->isEmpty()) {
            /** @var Address $address */
            foreach ($customer->getAddresses() as $address) {
                if (!$stateManager->isInState($address->getSyncState(), Address::MAGENTO_REMOVED)) {
                    $stateManager->addState($address, 'syncState', Address::SYNC_TO_MAGENTO);
                }
            }
        }
    }

    /**
     * @param array $item
     */
    protected function writeExistingItem(array $item)
    {
        /** @var Customer $entity */
        $entity = $this->getEntity();

        $customerId = $item[self::CUSTOMER_ID_KEY];

        try {
            $remoteData = $this->transport->getCustomerInfo($customerId);
            $item = $this->getStrategy()->merge(
                $this->getEntityChangeSet(),
                $item,
                $remoteData,
                $this->getTwoWaySyncStrategy()
            );

            $this->stepExecution->getJobExecution()
                ->getExecutionContext()
                ->put(self::CONTEXT_CUSTOMER_POST_PROCESS, [$item]);

            $result = $this->transport->updateCustomer($customerId, $item);

            if ($result) {
                $this->markSynced($entity);

                $this->logger->info(
                    sprintf('Customer with id %s successfully updated with data %s', $customerId, json_encode($item))
                );
            } else {
                $this->logger->error(sprintf('Customer with id %s was not updated', $customerId));
            }
        } catch (TransportException $e) {
            if ($e->getFaultCode() == self::FAULT_CODE_NOT_EXISTS) {
                $this->markRemoved($entity);
            }

            $this->logger->error($e->getMessage());
            $this->stepExecution->addFailureException($e);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->stepExecution->addFailureException($e);
        }
    }

    /**
     * @param Customer $customer
     */
    protected function markRemoved(Customer $customer)
    {
        $this->getStateManager()->addState($customer, 'syncState', Customer::MAGENTO_REMOVED);
        $this->markAddressesRemoved($customer);
    }

    /**
     * @param Customer $customer
     */
    protected function markAddressesRemoved(Customer $customer)
    {
        if (!$customer->getAddresses()->isEmpty()) {
            /** @var Address $address */
            foreach ($customer->getAddresses() as $address) {
                $address->setSyncState(Address::MAGENTO_REMOVED);
            }
        }
    }
}
