<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\AddressRelation;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoAddress;
use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\CustomerNormalizer;

class AddOrUpdateCustomer implements StrategyInterface, ContextAwareInterface
{
    const ENTITY_NAME = 'OroCRMMagentoBundle:Customer';
    const GROUP_ENTITY_NAME = 'OroCRMMagentoBundle:CustomerGroup';
    const ADDRESS_RELATION_ENTITY = 'OroCRMMagentoBundle:AddressRelation';

    /** @var ImportStrategyHelper */
    protected $strategyHelper;

    /** @var ContextInterface */
    protected $importExportContext;

    /** @var array */
    protected $regionsCache = [];

    /** @var array */
    protected $mageRegionsCache = [];

    /**
     * @param ImportStrategyHelper $strategyHelper
     */
    public function __construct(ImportStrategyHelper $strategyHelper)
    {
        $this->strategyHelper = $strategyHelper;
    }

    /**
     * Process item strategy
     *
     * @param mixed $importedEntity
     * @return mixed|null
     */
    public function process($importedEntity)
    {
        $newEntity = $this->findAndReplaceEntity(
            $importedEntity,
            self::ENTITY_NAME,
            'originalId',
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
        if ($newEntity->getId()) {
            $newEntity->getContact()->addAccount($newEntity->getAccount());
            $newEntity->getAccount()->setDefaultContact($newEntity->getContact());
        }

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
        $entity
            ->setWebsite(
                $this->findAndReplaceEntity($website, CustomerNormalizer::WEBSITE_TYPE, 'code', $doNotUpdateFields)
            )
            ->setStore(
                $this->findAndReplaceEntity($store, CustomerNormalizer::STORE_TYPE, 'code', $doNotUpdateFields)
            )
            ->setGroup(
                $this->findAndReplaceEntity($group, CustomerNormalizer::GROUPS_TYPE, 'name', $doNotUpdateFields)
            );

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
    public function updateAddresses(Customer $entity, Collection $addresses)
    {
        /** $address - imported address */
        foreach ($addresses as $address) {
            // at this point imported address region have code equal to region_id in magento db field
            $mageRegionId = $address->getRegion() ? $address->getRegion()->getCode() : null;

            $originAddressId = $address->getId();
            $existingAddress = $entity->getAddressByOriginId($originAddressId);

            if ($existingAddress) {
                $this->strategyHelper->importEntity($existingAddress, $address, ['id', 'region', 'country']);
                $address = $existingAddress;
            }

            $this
                ->updateAddressCountryRegion($address, $mageRegionId)
                ->updateAddressTypes($address);

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

            $originAddressId = $address->getId();
            $address->setId(null);
            $mageRegionId = $address->getRegion() ? $address->getRegion()->getCode() : null;

            $this->updateAddressCountryRegion($address, $mageRegionId);


            $account->{'set'.ucfirst($key).'Address'}($address);
        }

        $entity->setAccount($account);

        return $this;
    }

    /**
     * @param mixed $entity
     * @param string $entityName
     * @param string $idFieldName
     * @param array $excludedProperties
     * @return mixed
     */
    protected function findAndReplaceEntity($entity, $entityName, $idFieldName = 'id', $excludedProperties = [])
    {
        $existingEntity = $this->getEntityOrNull($entity, $idFieldName, $entityName);

        if ($existingEntity) {
            $this->strategyHelper->importEntity($existingEntity, $entity, $excludedProperties);
            $entity = $existingEntity;
        } else {
            $entity->setId(null);
        }

        return $entity;
    }

    /**
     * @param Customer $entity
     * @return $this
     */
    protected function validateAndUpdateContext(Customer $entity)
    {
        // validate contact
        $validationErrors = $this->strategyHelper->validateEntity($entity);
        if ($validationErrors) {
            $this->importExportContext->incrementErrorEntriesCount();
            $this->strategyHelper->addValidationErrors($validationErrors, $this->importExportContext);
            return null;
        }

        // increment context counter
        if ($entity->getId()) {
            $this->importExportContext->incrementUpdateCount();
        } else {
            $this->importExportContext->incrementAddCount();
        }

        return $this;
    }

    /**
     * @param mixed $entity
     * @param string $entityIdField
     * @param string $entityClass
     * @return Customer|null
     */
    protected function getEntityOrNull($entity, $entityIdField, $entityClass)
    {
        $existingEntity = null;
        $entityId = $entity->{'get'.ucfirst($entityIdField)}();

        if ($entityId) {
            $existingEntity = $this->getEntityRepository($entityClass)->findOneBy([$entityIdField => $entityId]);
        }

        return $existingEntity ?: null;
    }

    /**
     * @param string $entityName
     * @return EntityRepository
     */
    protected function getEntityRepository($entityName)
    {
        return $this->getEntityManager($entityName)->getRepository($entityName);
    }

    /**
     * @param $entityName
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager($entityName)
    {
        return $this->strategyHelper->getEntityManager($entityName);
    }

    /**
     * {@inheritDoc}
     */
    public function setImportExportContext(ContextInterface $importExportContext)
    {
        $this->importExportContext = $importExportContext;
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
        $loadedTypes = $this->getEntityRepository('OroAddressBundle:AddressType')
            ->findBy(['name' => $types]);

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
        $countryCode = $address->getCountry()->getIso2Code();

        // country cache
        $this->regionsCache[$countryCode] = empty($this->regionsCache[$countryCode]) ?
            $this->findAndReplaceEntity(
                $address->getCountry(),
                'Oro\Bundle\AddressBundle\Entity\Country',
                'iso2Code',
                ['iso2Code', 'iso3Code', 'name']
            ) :
            $this->regionsCache[$countryCode];

        if (!empty($mageRegionId) && empty($this->mageRegionsCache[$mageRegionId])) {
            $this->mageRegionsCache[$mageRegionId] = $this->getEntityRepository(
                'OroCRM\Bundle\MagentoBundle\Entity\Region'
            )
            ->findOneBy(['regionId' => $mageRegionId]);
        }

        if (empty($this->mageRegionsCache[$mageRegionId])) {
            // use regionText instead
        } else {
            $mageRegion = $this->mageRegionsCache[$mageRegionId];
            $combinedCode = $mageRegion->getCombinedCode();

            // set ISO combined code
            $address->getRegion()->setCombinedCode($combinedCode);

            $this->regionsCache[$combinedCode] = empty($this->regionsCache[$combinedCode]) ?
                $this->getEntityOrNull($address->getRegion(), 'combinedCode', 'Oro\Bundle\AddressBundle\Entity\Region'):
                $this->regionsCache[$combinedCode];

            $address->setRegion($this->regionsCache[$combinedCode]);
        }

        if (empty($this->regionsCache[$countryCode])) {
            throw new InvalidItemException(sprintf('Unable to find country by code "%s"', $countryCode), []);
        }
        $address->setCountry($this->regionsCache[$countryCode]);

        return $this;
    }
}
