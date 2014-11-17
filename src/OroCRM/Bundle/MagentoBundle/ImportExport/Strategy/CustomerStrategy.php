<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\Common\Collections\Collection;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\ContactImportHelper;

class CustomerStrategy extends BaseStrategy
{
    /** @var array */
    protected $storeEntityCache = [];

    /** @var array */
    protected $websiteEntityCache = [];

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

        // update all related entities
        $this->updateStoresAndGroup(
            $localEntity,
            $remoteEntity->getStore(),
            $remoteEntity->getWebsite(),
            $remoteEntity->getGroup()
        );

        // account and contact for new customer should be created automatically
        // by the appropriate queued process to improve initial import performance
        if ($localEntity->getId()) {
            $this->updateContact($remoteEntity, $localEntity, $remoteEntity->getContact());
            if ($localEntity->getAccount()) {
                $localEntity->getAccount()->setDefaultContact($localEntity->getContact());
            }
        } else {
            $localEntity->setContact(null);
            $localEntity->setAccount(null);
        }

        // VAT must be stored in percent representation
        $vat = $remoteEntity->getVat();
        if (null !== $vat) {
            $remoteEntity->setVat((float)$vat / 100);
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
     * @param Website       $website
     * @param CustomerGroup $group
     *
     * @return $this
     */
    protected function updateStoresAndGroup(Customer $entity, Store $store, Website $website, CustomerGroup $group)
    {
        if (!isset($this->websiteEntityCache[$website->getCode()])) {
            $this->websiteEntityCache[$website->getCode()] = $this->findAndReplaceEntity(
                $website,
                MagentoConnectorInterface::WEBSITE_TYPE,
                [
                    'code'     => $website->getCode(),
                    'channel'  => $website->getChannel(),
                    'originId' => $website->getOriginId()
                ],
                ['id', 'code', 'channel']
            );
        }
        $this->websiteEntityCache[$website->getCode()] = $this->merge($this->websiteEntityCache[$website->getCode()]);

        if (!isset($this->storeEntityCache[$store->getCode()])) {
            $this->storeEntityCache[$store->getCode()] = $this->findAndReplaceEntity(
                $store,
                MagentoConnectorInterface::STORE_TYPE,
                [
                    'code'     => $store->getCode(),
                    'channel'  => $store->getChannel(),
                    'originId' => $store->getOriginId()
                ],
                ['id', 'code', 'website', 'channel']
            );
        }
        $this->storeEntityCache[$store->getCode()] = $this->merge($this->storeEntityCache[$store->getCode()]);

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

        $entity
            ->setWebsite($this->websiteEntityCache[$website->getCode()])
            ->setStore($this->storeEntityCache[$store->getCode()])
            ->setGroup($this->groupEntityCache[$group->getName()]);

        $entity->getStore()->setWebsite($entity->getWebsite());
    }

    /**
     * Update $entity with new contact data
     *
     * @param Customer $remoteData
     * @param Customer $localData
     * @param Contact  $contact
     */
    protected function updateContact(Customer $remoteData, Customer $localData, Contact $contact)
    {
        $helper = new ContactImportHelper($localData->getChannel(), $this->addressHelper);

        if ($localData->getContact() && $localData->getContact()->getId()) {
            $helper->merge($remoteData, $localData, $localData->getContact());
        } else {
            $addresses = $localData->getAddresses();
            // loop by imported addresses, add new only
            /** @var \OroCRM\Bundle\ContactBundle\Entity\ContactAddress $address */
            foreach ($contact->getAddresses() as $key => $address) {
                $helper->prepareAddress($address);

                if (!$address->getCountry()) {
                    $contact->removeAddress($address);
                    continue;
                }
                // @TODO find possible solution
                // guess parent address by key
                if ($entity = $addresses->get($key)) {
                    $entity->setContactAddress($address);
                }
            }

            // @TODO find possible solution
            // guess parent $phone by key
            foreach ($contact->getPhones() as $key => $phone) {
                $contactPhone = $this->getContactPhoneFromContact($contact, $phone);
                if ($entity = $addresses->get($key)) {
                    $entity->setContactPhone($contactPhone ? $contactPhone : $phone);
                }
            }

            // populate default owner only for new contacts
            $this->defaultOwnerHelper->populateChannelOwner($contact, $localData->getChannel());
            $localData->setContact($contact);
        }
    }

    /**
     * Filtered phone by phone number from contact and return entity or null
     *
     * @param Contact      $contact
     * @param ContactPhone $contactPhone
     *
     * @return ContactPhone|null
     */
    protected function getContactPhoneFromContact(Contact $contact, ContactPhone $contactPhone)
    {
        foreach ($contact->getPhones() as $phone) {
            if ($phone->getPhone() === $contactPhone->getPhone()) {
                $hash = spl_object_hash($phone);
                if (array_key_exists($hash, $this->processedEntities)) {
                    // skip if contact phone used for previously imported phone
                    continue;
                }

                $this->processedEntities[$hash] = $phone;

                return $phone;
            }
        }

        return null;
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

        /** $address - imported address */
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
