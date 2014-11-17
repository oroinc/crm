<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\Common\Collections\Collection;

use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\ContactImportHelper;

class CustomerStrategy extends BaseStrategy
{
    /** @var array */
    protected $groupEntityCache = [];

    /** @var array */
    protected $processedEntities = [];

    /** @var array */
    protected static $fieldsForManualUpdate = [
        'id',
        'contact',
        'account',
        'website',
        'store',
        'group',
        'carts',
        'orders',
        'addresses',
        'lifetime',
        'owner',
        'organization',
        'channel',
        'dataChannel'
    ];

    /** @var StoreStrategy */
    protected $storeStrategy;

    /**
     * @param StoreStrategy $storeStrategy
     */
    public function setStoreStrategy(StoreStrategy $storeStrategy)
    {
        $this->storeStrategy = $storeStrategy;
    }

    /**
     * Update/Create customer and related entities based on remote data
     *
     * @param Customer $remoteEntity Denormalized remote data
     *
     * @return Customer|null
     */
    public function process($remoteEntity)
    {
        /** @var Customer $localEntity */
        $localEntity = $this->getEntityByCriteria(
            ['originId' => $remoteEntity->getOriginId(), 'channel' => $remoteEntity->getChannel()],
            $remoteEntity
        );

        if (!$localEntity) {
            $localEntity = $remoteEntity;

            // populate owner only for newly created entities
            $this->defaultOwnerHelper->populateChannelOwner($localEntity, $localEntity->getChannel());
        }

        // update store/website/customerGroup related entities
        $this->updateStoresAndGroup($localEntity, $remoteEntity->getStore(), $remoteEntity->getGroup());

        // account and contact for new customer should be created automatically
        // by the appropriate queued process to improve initial import performance
        if ($localEntity->getId() && $localEntity->getContact() && $localEntity->getContact()->getId()) {
            $helper = new ContactImportHelper($localEntity->getChannel(), $this->addressHelper);
            $helper->merge($remoteEntity, $localEntity, $localEntity->getContact());
        } else {
            $localEntity->setContact(null);
            $localEntity->setAccount(null);
        }

        // modify local entity after all relations done
        $this->strategyHelper->importEntity($localEntity, $remoteEntity, self::$fieldsForManualUpdate);

        $this->updateAddresses($localEntity, $remoteEntity->getAddresses());

        // validate and update context - increment counter or add validation error
        return $this->validateAndUpdateContext($localEntity);
    }

    /**
     * Update $entity with new data from imported $store, $website, $group
     *
     * @param Customer      $entity
     * @param Store         $store
     * @param CustomerGroup $group
     *
     * @return $this
     */
    protected function updateStoresAndGroup(Customer $entity, Store $store, CustomerGroup $group)
    {
        if (!isset($this->groupEntityCache[$group->getName()])) {
            $this->groupEntityCache[$group->getName()] = $this->findAndReplaceEntity(
                $group,
                MagentoConnectorInterface::CUSTOMER_GROUPS_TYPE,
                [
                    'name'     => $group->getName(),
                    'channel'  => $group->getChannel(),
                    'originId' => $group->getOriginId()
                ],
                ['id', 'channel']
            );
        }
        $this->groupEntityCache[$group->getName()] = $this->merge($this->groupEntityCache[$group->getName()]);
        $this->groupEntityCache[$group->getName()]->setChannel($this->merge($group->getChannel()));

        $store = $this->storeStrategy->process($store);

        $entity
            ->setStore($store)
            ->setWebsite($store->getWebsite())
            ->setGroup($this->groupEntityCache[$group->getName()]);
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param Customer             $entity
     * @param Collection|Address[] $addresses
     *
     * @return $this
     */
    protected function updateAddresses(Customer $entity, Collection $addresses)
    {
        // force option enforce re-import of all addresses
        if ($this->context->getOption('force') && $entity->getId()) {
            $entity->getAddresses()->clear();
        }

        $processedRemote = [];

        /** @var $address - imported address */
        foreach ($addresses as $address) {
            // at this point imported address region have code equal to region_id in magento db field
            $mageRegionId = $address->getRegion() ? $address->getRegion()->getCode() : null;

            $originAddressId = $address->getOriginId();
            if ($originAddressId && !$this->context->getOption('force')) {
                $existingAddress = $entity->getAddressByOriginId($originAddressId);
                if ($existingAddress) {
                    $this->strategyHelper->importEntity(
                        $existingAddress,
                        $address,
                        [
                            'id',
                            'region',
                            'country',
                            'owner',
                            'contactAddress',
                            'created',
                            'updated',
                            'contactPhone',
                            'types'
                        ]
                    );
                    // set remote data for further processing
                    $existingAddress->setRegion($address->getRegion());
                    $existingAddress->setCountry($address->getCountry());
                    $this->addressHelper->mergeAddressTypes($existingAddress, $address);

                    $address = $existingAddress;
                }
            }

            $this->updateAddressCountryRegion($address, $mageRegionId);
            if ($address->getCountry()) {
                $this->updateAddressTypes($address);

                $address->setOwner($entity);
                $entity->addAddress($address);
                $processedRemote[] = $address;
            }

            $contact = $entity->getContact();
            if ($contact) {
                $contactAddress = $address->getContactAddress();
                $contactPhone   = $address->getContactPhone();
                if ($contactAddress) {
                    $contactAddress->setOwner($contact);
                }
                if ($contactPhone) {
                    $contactPhone->setOwner($contact);
                }
            } else {
                $address->setContactAddress(null);
                $address->setContactPhone(null);
            }
        }

        // remove not processed addresses
        $toRemove = $entity->getAddresses()->filter(
            function (Address $address) use ($processedRemote) {
                return !in_array($address, $processedRemote, true);
            }
        );
        foreach ($toRemove as $address) {
            $entity->removeAddress($address);
        }
    }
}
