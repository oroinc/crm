<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Writer;

use Oro\Bundle\IntegrationBundle\Exception\TransportException;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\MagentoBundle\Entity\Repository\RegionRepository;
use Oro\Bundle\MagentoBundle\Service\CustomerStateHandler;

class CustomerAddressExportWriter extends AbstractExportWriter
{
    const CUSTOMER_ADDRESS_ID_KEY = 'customer_address_id';
    const CUSTOMER_ID_KEY = 'customer_id';
    const FAULT_CODE_NOT_EXISTS = '103';
    const CONTEXT_CUSTOMER_ADDRESS_POST_PROCESS = 'postProcessCustomerAddress';

    /**
     * @var string
     */
    protected $magentoRegionClass;

    /**
     * @var CustomerStateHandler
     */
    protected $stateHandler;

    /**
     * @param CustomerStateHandler $stateHandler
     * @return CustomerAddressExportWriter
     */
    public function setStateHandler($stateHandler)
    {
        $this->stateHandler = $stateHandler;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        /** @var Address $entity */
        $entity = $this->getEntity();
        if ($this->stateHandler->isAddressRemoved($entity)) {
            return;
        }

        $item = reset($items);
        if (!empty($item['region_id'])) {
            $item['region_id'] = $this->getMagentoRegionIdByCombinedCode($item['region_id']);
            if (empty($item['region_id'])) {
                $item['region'] = $entity->getRegionName();
            }
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

        parent::write([$entity]);
    }

    /**
     * @param array $item
     */
    protected function writeNewItem(array $item)
    {
        /** @var Address $entity */
        $entity = $this->getEntity();

        try {
            $customerId = $item[self::CUSTOMER_ID_KEY];
            $customerAddressId = $this->transport->createCustomerAddress($customerId, $item);
            $entity->setOriginId($customerAddressId);
            $this->stateHandler->markAddressSynced($entity);

            $this->logger->info(
                sprintf(
                    'Customer address with id %s for customer %s successfully created with data %s',
                    $customerAddressId,
                    $customerId,
                    json_encode($item)
                )
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->stepExecution->addFailureException($e);
        }
    }

    /**
     * @param array $item
     */
    protected function writeExistingItem(array $item)
    {
        $customerAddressId = $item[self::CUSTOMER_ADDRESS_ID_KEY];

        /** @var Address $entity */
        $entity = $this->getEntity();

        try {
            $remoteData = $this->transport->getCustomerAddressInfo($customerAddressId);
            $remoteData[self::CUSTOMER_ID_KEY] = $entity->getOwner()->getOriginId();

            $item = $this->getStrategy()->merge(
                $this->getEntityChangeSet(),
                $item,
                $remoteData,
                $this->getTwoWaySyncStrategy(),
                ['is_default_shipping', 'is_default_billing']
            );

            $this->stepExecution->getJobExecution()
                ->getExecutionContext()
                ->put(self::CONTEXT_CUSTOMER_ADDRESS_POST_PROCESS, [$item]);

            $result = $this->transport->updateCustomerAddress($customerAddressId, $item);

            if ($result) {
                $this->stateHandler->markAddressSynced($entity);

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
        } catch (TransportException $e) {
            if ($e->getFaultCode() == self::FAULT_CODE_NOT_EXISTS) {
                $this->stateHandler->markAddressRemoved($entity);
            }

            $this->logger->error($e->getMessage());
            $this->stepExecution->addFailureException($e);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->stepExecution->addFailureException($e);
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
}
