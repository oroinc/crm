<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Writer;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CustomerExportWriter extends AbstractExportWriter
{
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
        if (empty($item['customer_id'])) {
            try {
                $customerId = $this->transport->createCustomer($item);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());

                return;
            }

            $this->logger->info(
                sprintf('Customer with id %s successfully created with data %s', $customerId, json_encode($item))
            );

            /** @var Customer $customer */
            $customer = $this->getEntity();
            $customer->setOriginId($customerId);

            parent::write([$customer]);
        } else {
            $customerId = $item['customer_id'];

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
    }
}
