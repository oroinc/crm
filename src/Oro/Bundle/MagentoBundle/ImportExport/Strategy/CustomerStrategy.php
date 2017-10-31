<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\MagentoBundle\Provider\Reader\ContextCustomerReader;

class CustomerStrategy extends AbstractImportStrategy
{
    /**
     * @var Address[]
     */
    protected $importingAddresses = [];

    /**
     * @param Customer $entity
     * @return Customer
     */
    protected function beforeProcessEntity($entity)
    {
        $this->importingAddresses = [];
        $importingAddresses = $entity->getAddresses();
        if ($importingAddresses) {
            foreach ($importingAddresses as $address) {
                if ($address->getSyncState() !== Address::SYNC_TO_MAGENTO) {
                    $originId = $address->getOriginId();
                    if ($address->getRegion() && $address->getCountry()) {
                        // at this point imported address region have combinedCode equal to region_id in magento db
                        $this->addressHelper->addMageRegionId(
                            Address::class,
                            $originId,
                            $address->getRegion()->getCode()
                        );
                        /**
                         * We must run this method here because it set regionText to address to prevent error of
                         * "Not found entity". Real Region will be set in "afterProcessEntity" method
                         */
                        $this->addressHelper->updateRegionByMagentoRegionId($address, $originId, true);
                    }

                    $this->importingAddresses[$originId] = $address;
                }
            }
        }

        return parent::beforeProcessEntity($entity);
    }

    /**
     * @param Customer $entity
     * @return Customer
     */
    protected function afterProcessEntity($entity)
    {
        $this->processAddresses($entity);

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        if (!$entity->getImportedAt()) {
            $entity->setImportedAt($now);
        }
        $entity->setSyncedAt($now);

        $this->addressHelper->resetMageRegionIdCache(Address::class);
        $this->appendDataToContext(ContextCustomerReader::CONTEXT_POST_PROCESS_CUSTOMERS, $entity->getOriginId());

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param Customer $entity
     */
    protected function processAddresses(Customer $entity)
    {
        if (!$entity->getAddresses()->isEmpty()) {
            /** @var Address $address */
            foreach ($entity->getAddresses() as $address) {
                if ($address->getSyncState() !== Address::SYNC_TO_MAGENTO) {
                    $originId = $address->getOriginId();
                    if (array_key_exists($originId, $this->importingAddresses)) {
                        $remoteAddress = $this->importingAddresses[$originId];
                        $this->addressHelper->mergeAddressTypes($address, $remoteAddress);

                        if ($address->getCountry()) {
                            $this->addressHelper->updateRegionByMagentoRegionId($address, $originId);
                        }
                    }
                }
                if ($address->getCountry()) {
                    $address->setCountryText(null);
                }
                $address->setOwner($entity);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        $existingEntity = null;

        if ($entity instanceof Customer) {
            /** @var Customer $existingEntity */
            $existingEntity = parent::findExistingEntity($entity, $searchContext);

            if (!$existingEntity) {
                $website = $this->databaseHelper->findOneBy(
                    Website::class,
                    [
                        'originId' => $entity->getWebsite()->getOriginId(),
                        'channel' => $entity->getChannel()
                    ]
                );
                $existingEntity = $this->databaseHelper->findOneBy(
                    Customer::class,
                    [
                        'email' => $entity->getEmail(),
                        'channel' => $entity->getChannel(),
                        'website' => $website,
                        'originId' => null
                    ]
                );
                if ($existingEntity && $existingEntity->getId()) {
                    if ($existingEntity->isGuest()) {
                        $existingEntity->setGuest(false);
                        $existingEntity->setIsActive(true);
                    }
                    if ($entity->getOriginId()) {
                        $existingEntity->setOriginId($entity->getOriginId());
                    }
                }
            }
        } elseif ($entity instanceof Region) {
            /** @var \Oro\Bundle\MagentoBundle\Entity\Region $existingEntity */
            $existingEntity = $this->findRegionEntity($entity, $entity->getCombinedCode());
        } else {
            /** @var Customer $existingEntity */
            $existingEntity = parent::findExistingEntity($entity, $searchContext);
        }

        return $existingEntity;
    }
}
