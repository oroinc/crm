<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Writer;

use Oro\Bundle\IntegrationBundle\Exception\TransportException;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\RegionRepository;

class CustomerAddressExportWriter extends AbstractExportWriter
{
    const CUSTOMER_ADDRESS_ID_KEY = 'customer_address_id';
    const CUSTOMER_ID_KEY = 'customer_id';
    const FAULT_CODE_NOT_EXISTS = 102;

    /**
     * @var string
     */
    protected $magentoRegionClass;

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $item = reset($items);
        if (!empty($item['region_id'])) {
            $item['region_id'] = $this->getMagentoRegionIdByCombinedCode($item['region_id']);
        }

        if (!$item) {
            $this->logger->error('Wrong Customer Address data', (array)$item);

            return;
        }

        $this->transport->init($this->getChannel()->getTransport());
        if (empty($item[self::CUSTOMER_ADDRESS_ID_KEY])) {
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
            $customerId = $item[self::CUSTOMER_ID_KEY];
            $customerAddressId = $this->transport->createCustomerAddress($customerId, $item);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return;
        }

        $this->logger->info(
            sprintf(
                'Customer address with id %s for customer %s successfully created with data %s',
                $customerAddressId,
                $customerId,
                json_encode($item)
            )
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
        $customerAddressId = $item[self::CUSTOMER_ADDRESS_ID_KEY];

        try {
            $result = $this->transport->updateCustomerAddress($customerAddressId, $item);
        } catch (TransportException $e) {
            if ($e->getFaultCode() === self::FAULT_CODE_NOT_EXISTS) {
                $this->markAddressRemoved($this->getEntity());
            }

            $this->logger->error($e->getMessage());

            return;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return;
        }

        if ($result) {
            $this->logger->info(
                sprintf(
                    'Customer address with id %s successfully updated with data %s',
                    $customerAddressId,
                    json_encode($item)
                )
            );
        } else {
            $this->logger->error(sprintf('Customer address with id %s was not updated', $customerAddressId));
        }
    }

    /**
     * @param string $magentoRegionClass
     * @return CustomerAddressExportWriter
     */
    public function setMagentoRegionClass($magentoRegionClass)
    {
        $this->magentoRegionClass = $magentoRegionClass;

        return $this;
    }

    /**
     * @param string $combinedCode
     * @return int
     */
    protected function getMagentoRegionIdByCombinedCode($combinedCode)
    {
        /** @var RegionRepository $magentoRegionRepository */
        $magentoRegionRepository = $this->registry->getRepository($this->magentoRegionClass);

        return $magentoRegionRepository->getMagentoRegionIdByCombinedCode($combinedCode);
    }

    /**
     * @param Address $address
     */
    protected function markAddressRemoved(Address $address)
    {
        // TODO: Use state manager and set STATE_MAGENTO_REMOVED to $address
    }
}
