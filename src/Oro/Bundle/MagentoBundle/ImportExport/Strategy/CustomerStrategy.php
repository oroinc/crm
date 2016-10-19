<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\Reader\ContextCustomerReader;

class CustomerStrategy extends AbstractImportStrategy
{
    /**
     * @var Address[]
     */
    protected $importingAddresses = [];

    /**
     * @var array
     */
    protected $addressRegions = [];

    /**
     * @param Customer $entity
     * @return Customer
     */
    protected function beforeProcessEntity($entity)
    {
        $this->importingAddresses = [];
        $this->addressRegions = [];
        $importingAddresses = $entity->getAddresses();
        if ($importingAddresses) {
            foreach ($importingAddresses as $address) {
                if ($address->getSyncState() !== Address::SYNC_TO_MAGENTO) {
                    $originId = $address->getOriginId();
                    $this->importingAddresses[$originId] = $address;

                    if ($address->getRegion()) {
                        $this->addressRegions[$originId] = $address->getRegion()->getCombinedCode();
                    } else {
                        $this->addressRegions[$originId] = null;
                    }
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

                        if (!empty($this->addressRegions[$originId]) && $address->getCountry()) {
                            $this->addressHelper->updateRegionByMagentoRegionId(
                                $address,
                                $address->getCountry()->getIso2Code(),
                                $this->addressRegions[$originId]
                            );
                        }
                    }
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
            $website = $this->databaseHelper->findOneBy(
                'Oro\Bundle\MagentoBundle\Entity\Website',
                [
                    'originId' => $entity->getWebsite()->getOriginId(),
                    'channel' => $entity->getChannel()
                ]
            );

            if ($website) {
                $searchContext['website'] = $website;
            }
            /** @var Customer $existingEntity */
            $existingEntity = parent::findExistingEntity($entity, $searchContext);

            if (!$existingEntity) {
                $existingEntity = $this->databaseHelper->findOneBy(
                    'Oro\Bundle\MagentoBundle\Entity\Customer',
                    [
                        'email' => $entity->getEmail(),
                        'channel' => $entity->getChannel(),
                        'website' => $website
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
