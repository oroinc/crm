<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\CustomerNormalizer;
use OroCRM\Bundle\MagentoBundle\Provider\StoreConnector;

class AddOrUpdateCustomer extends BaseStrategy
{
    const ENTITY_NAME             = 'OroCRMMagentoBundle:Customer';
    const GROUP_ENTITY_NAME       = 'OroCRMMagentoBundle:CustomerGroup';
    const ADDRESS_RELATION_ENTITY = 'OroCRMMagentoBundle:AddressRelation';

    /** @var array */
    protected $regionsCache = [];

    /** @var array */
    protected $countriesCache = [];

    /** @var array */
    protected $mageRegionsCache = [];

    /** @var array */
    protected $storeEntityCache = [];

    /** @var array */
    protected $websiteEntityCache = [];

    /** @var array */
    protected $groupEntityCache = [];

    /**
     * Process item strategy
     *
     * @param mixed $importedEntity
     * @return mixed|null
     */
    public function process($importedEntity)
    {
        // TODO: look for customer by originId and channelId
        $newEntity = $this->findAndReplaceEntity(
            $importedEntity,
            self::ENTITY_NAME,
            'originId',
            ['id', 'contact', 'account', 'website', 'store', 'group', 'addresses']
        );

        // update all related entities
        $this->updateStoresAndGroup(
            $newEntity,
            $importedEntity->getStore(),
            $importedEntity->getWebsite(),
            $importedEntity->getGroup()
        );

        $this->updateAddresses($newEntity, $importedEntity->getAddresses())
             ->updateContact($newEntity, $importedEntity->getContact(), true)
             ->updateAccount($newEntity, $importedEntity->getAccount());

        // set relations
        $newEntity->getContact()->addAccount($newEntity->getAccount());
        $newEntity->getAccount()->setDefaultContact($newEntity->getContact());

        // validate and update context - increment counter or add validation error
        $this->validateAndUpdateContext($newEntity);

        return $newEntity;
    }

    /**
     * Update $entity with new data from imported $store, $website, $group
     *
     * @param Customer $entity
     * @param Store $store
     * @param Website $website
     * @param CustomerGroup $group
     *
     * @return $this
     */
    protected function updateStoresAndGroup(Customer $entity, Store $store, Website $website, CustomerGroup $group)
    {
        // do not allow to change code/website name by imported entity
        $doNotUpdateFields = ['id', 'code', 'name'];

        if (!isset($this->websiteEntityCache[$website->getCode()])) {
            $this->websiteEntityCache[$website->getCode()] = $this->findAndReplaceEntity(
                $website,
                StoreConnector::WEBSITE_TYPE,
                'code',
                $doNotUpdateFields
            );
        }
        $this->websiteEntityCache[$website->getCode()] = $this->merge($this->websiteEntityCache[$website->getCode()]);

        if (!isset($this->storeEntityCache[$store->getCode()])) {
            $this->storeEntityCache[$store->getCode()] = $this->findAndReplaceEntity(
                $store,
                StoreConnector::STORE_TYPE,
                'code',
                $doNotUpdateFields
            );
        }
        $this->storeEntityCache[$store->getCode()] = $this->merge($this->storeEntityCache[$store->getCode()]);

        if (!isset($this->groupEntityCache[$group->getName()])) {
            $this->groupEntityCache[$group->getName()] = $this->findAndReplaceEntity(
                $group,
                CustomerNormalizer::GROUPS_TYPE,
                'name',
                $doNotUpdateFields
            );
        }
        $this->groupEntityCache[$group->getName()] = $this->merge($this->groupEntityCache[$group->getName()]);

        $entity
            ->setWebsite($this->websiteEntityCache[$website->getCode()])
            ->setStore($this->storeEntityCache[$store->getCode()])
            ->setGroup($this->groupEntityCache[$group->getName()]);

        $entity->getStore()->setWebsite($entity->getWebsite());

        return $this;
    }

    /**
     * Update $entity with new contact data
     * updating contact data is not allowed
     *
     * @param Customer $entity
     * @param Contact $contact
     * @return $this
     */
    protected function updateContact(Customer $entity, Contact $contact)
    {
        // update not allowed
        if ($entity->getContact() && $entity->getContact()->getId()) {
            return $this;
        }

        // loop by imported addresses, add new only
        foreach ($contact->getAddresses() as $address) {
            // at this point imported address region have code equal to region_id in magento db field
            $mageRegionId = $address->getRegion() ? $address->getRegion()->getCode() : null;
            //$originAddressId = $address->getId();
            $address->setId(null);

            $this
                ->updateAddressCountryRegion($address, $mageRegionId)
                ->updateAddressTypes($address);
        }

        $entity->setContact($contact);

        return $this;
    }

    /**
     * @param Customer $entity
     * @param Collection|Address[] $addresses
     * @return $this
     */
    protected function updateAddresses(Customer $entity, Collection $addresses)
    {
        /** $address - imported address */
        foreach ($addresses as $address) {
            // at this point imported address region have code equal to region_id in magento db field
            $mageRegionId = $address->getRegion() ? $address->getRegion()->getCode() : null;

            $originAddressId = $address->getId();
            $address->setOriginId($originAddressId);
            $existingAddress = $entity->getAddressByOriginId($originAddressId);

            if ($existingAddress) {
                $this->strategyHelper->importEntity($existingAddress, $address, ['id', 'region', 'country']);
                $address = $existingAddress;
            }

            $this
                ->updateAddressCountryRegion($address, $mageRegionId)
                ->updateAddressTypes($address);

            $address->setOwner($entity);
            $entity->addAddress($address);
        }

        return $this;
    }

    /**
     * @param Customer $entity
     * @param Account $account
     * @return $this
     */
    protected function updateAccount(Customer $entity, Account $account)
    {
        /** @var Account $existingAccount */
        $existingAccount = $entity->getAccount();

        // update not allowed
        if ($existingAccount && $existingAccount->getId()) {
            return $this;
        }

        $addresses = [
            AddressType::TYPE_SHIPPING => $account->getShippingAddress(),
            AddressType::TYPE_BILLING => $account->getBillingAddress()
        ];

        foreach ($addresses as $key => $address) {
            if (empty($address)) {
                continue;
            }

            //$originAddressId = $address->getId();
            $address->setId(null);
            $mageRegionId = $address->getRegion() ? $address->getRegion()->getCode() : null;

            $this->updateAddressCountryRegion($address, $mageRegionId);


            $account->{'set'.ucfirst($key).'Address'}($address);
        }

        $entity->setAccount($account);

        return $this;
    }

    /**
     * @param AbstractTypedAddress $address
     * @return $this
     */
    protected function updateAddressTypes(AbstractTypedAddress $address)
    {
        // update address type
        $types = $address->getTypeNames();
        if (empty($types)) {
            return $this;
        }

        $address->getTypes()->clear();
        $loadedTypes = $this->getEntityRepository('OroAddressBundle:AddressType')->findBy(['name' => $types]);

        foreach ($loadedTypes as $type) {
            $address->addType($type);
        }

        return $this;
    }

    /**
     * @param AbstractAddress $address
     * @param int $mageRegionId
     * @return $this
     *
     * @throws InvalidItemException
     */
    protected function updateAddressCountryRegion(AbstractAddress $address, $mageRegionId)
    {
        /*
         * @TODO review this implementation
         */
        $countryCode = $address->getCountry()->getIso2Code();

        $country = $this->getAddressCountryByCode($address, $countryCode);
        $address->setCountry($country);

        if (!empty($mageRegionId) && empty($this->mageRegionsCache[$mageRegionId])) {
            $this->mageRegionsCache[$mageRegionId] = $this->getEntityRepository(
                'OroCRM\Bundle\MagentoBundle\Entity\Region'
            )->findOneBy(['regionId' => $mageRegionId]);
        }

        if (!empty($this->mageRegionsCache[$mageRegionId])) {
            $mageRegion   = $this->mageRegionsCache[$mageRegionId];
            $combinedCode = $mageRegion->getCombinedCode();

            // set ISO combined code
            $address->getRegion()->setCombinedCode($combinedCode);

            $this->regionsCache[$combinedCode] = empty($this->regionsCache[$combinedCode]) ?
                $this->getEntityOrNull(
                    $address->getRegion(),
                    'combinedCode',
                    'Oro\Bundle\AddressBundle\Entity\Region'
                ) :
                $this->regionsCache[$combinedCode];

            // no region found in system db for corresponding magento region, use region text
            if (empty($this->regionsCache[$combinedCode])) {
                $address->setRegion(null);
            } else {
                $this->regionsCache[$combinedCode] = $this->merge($this->regionsCache[$combinedCode]);
                $address->setRegion($this->regionsCache[$combinedCode]);
                $address->setRegionText(null);
            }
        }

        return $this;
    }

    /**
     * @param AbstractAddress $address
     * @param string          $countryCode
     *
     * @throws \Oro\Bundle\BatchBundle\Item\InvalidItemException
     * @return object
     */
    protected function getAddressCountryByCode(AbstractAddress $address, $countryCode)
    {
        $this->countriesCache[$countryCode] = empty($this->countriesCache[$countryCode])
            ? $this->findAndReplaceEntity(
                $address->getCountry(),
                'Oro\Bundle\AddressBundle\Entity\Country',
                'iso2Code',
                ['iso2Code', 'iso3Code', 'name']
            )
            : $this->merge($this->countriesCache[$countryCode]);

        if (empty($this->countriesCache[$countryCode])) {
            throw new InvalidItemException(sprintf('Unable to find country by code "%s"', $countryCode), []);
        }

        return $this->countriesCache[$countryCode];
    }
}
