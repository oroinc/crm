<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Writer;

use Oro\Bundle\IntegrationBundle\Exception\TransportException;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CustomerExportWriter extends AbstractExportWriter
{
    const CUSTOMER_ID_KEY = 'customer_id';
    const FAULT_CODE_NOT_EXISTS = 102;

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
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
    }

    /**
     * @param array $item
     */
    protected function writeNewItem(array $item)
    {
        try {
            $customerId = $this->transport->createCustomer($item);
        } catch (TransportException $e) {
            if ($e->getFaultCode() === self::FAULT_CODE_NOT_EXISTS) {
                $this->markCustomerRemoved($this->getEntity());
            }

            $this->logger->error($e->getMessage());

            return;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return;
        }

        $this->logger->info(
            sprintf('Customer with id %s successfully created with data %s', $customerId, json_encode($item))
        );

        $entity = $this->getEntity();
        $entity->setOriginId($customerId);

        parent::write([$entity]);
    }

    /**
     * @param array $item
     */
    protected function writeExistingItem(array $item)
    {
        $customerId = $item[self::CUSTOMER_ID_KEY];

        try {
            $result = $this->transport->updateCustomer($customerId, $item);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return;
        }

        if ($result) {
            $this->logger->info(
                sprintf('Customer with id %s successfully updated with data %s', $customerId, json_encode($item))
            );
        } else {
            $this->logger->error(sprintf('Customer with id %s was not updated', $customerId));
        }
    }

    /**
     * @param Customer $getEntity
     */
    protected function markCustomerRemoved(Customer $getEntity)
    {
        // TODO: Use state manager and set STATE_MAGENTO_REMOVED to $address
    }
}
